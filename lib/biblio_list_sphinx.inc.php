<?php
/**
 * biblio_list class
 * Class for generating list of bibliographic records from SPHINX index
 *
 * Copyright (C) 2010 Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

class biblio_list extends biblio_list_model
{
    protected $options = array('host' => '127.0.0.1', 'port' => 9312, 'index' => 'slims',
	    'mode' => null, 'timeout' => 0, 'filter' => '@last_update desc',
	    'filtervals' => array(), 'groupby' => null, 'groupsort' => null,
	    'sortby' => null, 'sortexpr' => null, 'distinct' => 'biblio_id',
	    'select' => null, 'limit' => 20, 'max_limit' => 500000,
	    'ranker' => null);
    protected $offset = 0;
    private $sphinx = null;
    private $sphinx_error = false;
    private $no_query = false;
    private $sphinx_no_result = false;

    /**
     * Class Constructor
     *
     * @param   object  $obj_db
     * @param   integer	$int_num_show
     */
    public function __construct($obj_db, $int_num_show) {
      parent::__construct($obj_db, $int_num_show);
	    if (!class_exists('SphinxClient')) {
	      throw new Exception('SPHINX API Library is not installed yet!');
	    } else {
	      $this->sphinx = new SphinxClient();
	      // check searchd status
	      $_sphinx_status = $this->sphinx->Status();
	      if (!$_sphinx_status) {
		      throw new Exception('SPHINX Server is not running! Please
			      check if it already configured correctly.');
	      }

	      // defaults
	      $this->options['mode'] = SPH_MATCH_EXTENDED2;
	      $this->options['ranker'] = SPH_RANK_PROXIMITY_BM25;

	      // get page number from http get var
	      if (!isset($_GET['page']) OR $_GET['page'] < 1){ $_page = 1; } else {
		      $_page = (integer)$_GET['page'];
	      }
  	    $this->current_page = $_page;

	      // count the row offset
	      if ($this->current_page <= 1) { $_offset = 0; } else {
		      $this->offset = ($this->current_page*$this->num2show) - $this->num2show;
	      }
	    }
    }


    /**
     * Compile SQL
     *
     * @return  string
     */
    public function compileSQL()
    {
	     $_sql_str = 'SELECT SQL_CALC_FOUND_ROWS index.biblio_id, index.title,
	     	 index.author, index.image, index.isbn_issn, index.labels, index.publisher, index.publish_place, index.publish_year
	     	 FROM search_biblio AS `index`';
	     if (isset($this->criteria['sql_criteria'])) {
	     	 $_sql_str .= ' WHERE '.$this->criteria['sql_criteria'];
	     } else if ($this->sphinx_no_result) {
	     	 $_sql_str .= " WHERE index.biblio_id<0";
	     } else {
	     	 $this->no_query = true;
	     	 $_sql_str .= " WHERE index.biblio_id IS NOT NULL";
	     }
	     // ordering
	     $_sql_str .= ' ORDER BY index.last_update DESC ';
	     // set limit when query is empty
	     if (!isset($this->criteria['sql_criteria']) || $this->no_query) {
	     	 $_sql_str .= ' LIMIT '.$this->offset.','.$this->num2show;
	     }
	     return $_sql_str;
    }


    /**
     * Method to print out document records
     *
     * @param   object  $obj_db
     * @param   integer $int_num2show
     * @param   boolean $bool_return_output
     * @return  string
     */
    public function getDocumentList($bool_return_output = true) {
	    global $sysconf;
	    if ($this->sphinx_error) {
	    	$this->resultset = false;
	    } else {
	        $_sql_str = $this->compileSQL();
	        if ($this->no_query) {
	    	  // start time
	    	  $_start = function_exists('microtime')?microtime(true):time();
	    	  // execute query
	    	  $this->resultset = $this->obj_db->query($_sql_str);
	    	  if ($this->obj_db->error) {
	    	      $this->query_error = $this->obj_db->error;
	    	  }
	    	  // get total number of rows from query
	    	  $_total_q = $this->obj_db->query('SELECT FOUND_ROWS()');
	    	  $_total_d = $_total_q->fetch_row();
	    	  $this->num_rows = $_total_d[0];
	    	  // end time
	    	  $_end = function_exists('microtime')?microtime(true):time();
	    	  $this->query_time = round($_end-$_start, 5);
	        } else {
	    	    $this->resultset = $this->obj_db->query($_sql_str);
	        }

	        if ($this->obj_db->error) {
	    	    $this->query_error = $this->obj_db->error;
	        }
	    }

	    if ($bool_return_output) {
	      // return the html result
	      return $this->makeOutput();
	    }
    }


    /**
     * Set sphinx search option
     *
     * @param	array  $arr_options
     * @return	void
     */
    public function setOptions($arr_options) {
	    $this->options = $arr_options;
    }


    /**
     * Method to set search criteria
     *
     * @param   string  $str_criteria
     * @return  void
     */
    public function setSQLcriteria($str_criteria) {
      if (!$str_criteria) return null;
      // defaults
      $_query_str = '';
      $_searched_fields = array();
      $_previous_field = '';
      $_boolean = '';
			$_b = '';
      // parse query
      $this->orig_query = $str_criteria;
      $_queries = simbio_tokenizeCQL($str_criteria, $this->searchable_fields, $this->stop_words, $this->queries_word_num_allowed);
      // var_dump($_queries);
      if (count($_queries) < 1) { return null; }
      // loop each query
	    // echo '<pre>'; var_dump($_queries); echo '</pre>';
      foreach ($_queries as $_num => $_query) {
        // field
        $_field = $_query['f'];
				if ($_previous_field <> $_field) {
						if ($_field != 'boolean') {
						  $_query_str .= '';
						} else {
						  $_query_str .= ')';
						}
				}

        //  break the loop if we meet `cql_end` field
        if ($_field == 'cql_end') { continue; }
	      // if field is boolean
	      if ($_field == 'boolean') {
		      if ($_query['b'] == '*') { $_query_str .= ' | '; } else { $_query_str .= ' & '; }
		      continue;
	      } else {
		      if ($_query['b'] == '-') {
						$_query_str .= ' -';
				  } else if ($_query['b'] == '*') {
						$_query_str .= ' | ';
				  } else {
						$_query_str .= ' ';
					}
		      $_q = @$this->obj_db->escape_string($_query['q']);
		      $_q = isset($_query['is_phrase'])?'"'.$_q.'"':$_q;
		      $_boolean = '';
	      }
				if ($_previous_field == $_field) {
          $_query_str .= $_q;
					continue;
				}
				$_previous_field = $_field;
        // for debugging purpose only
        // echo "<p>$_num. $_field -> $_boolean -> $_query_str</p><p>&nbsp;</p>";

	      // check fields
				$_q = $_b.$_q;
        switch ($_field) {
          case 'author' :
		      $_query_str .= " (@author $_q";
          break;
          case 'subject' :
		      $_query_str .= " (@topic $_q";
          break;
          case 'location' :
		      $_query_str .= " (@location $_q";
          break;
          case 'colltype' :
		      $_query_str .= " (@collection_types $_q";
          break;
          case 'itemcode' :
		      $_query_str .= " (@items $_q";
          break;
          case 'callnumber' :
		      $_query_str .= " (@call_number $_q";
          break;
          case 'itemcallnumber' :
		      $_query_str .= " (@item_call_number $_q";
          break;
          case 'class' :
		      $_query_str .= " (@classification $_q";
          break;
          case 'isbn' :
		      $_query_str .= " (@isbn_issn $_q";
          break;
          case 'publisher' :
		      $_query_str .= " (@publisher $_q";
          break;
          case 'publishyear' :
		      $_query_str .= " (@publish_year $_q";
          break;
          case 'gmd' :
		      $_query_str .= " (@gmd $_q";
          break;
          case 'notes' :
		      $_query_str .= " (@notes $_q";
          break;
          default :
		      $_query_str .= " (@title $_q";
          break;
        }
      }

      $_query_str .= ')';
	  
	    // check if query is empty
	    if (!$_query_str) {
	      $this->no_query = true;
	      $_sql_criteria = 'index.biblio_id IS NOT NULL';
	      $this->criteria = array('sql_criteria' => $_sql_criteria, 'searched_fields' => $_searched_fields);
	      return $this->criteria;
	    }

	    // set options
	    $this->sphinx->SetServer ( $this->options['host'], $this->options['port'] );
	    $this->sphinx->SetConnectTimeout ( $this->options['timeout'] );
	    $this->sphinx->SetArrayResult ( true );
	    $this->sphinx->SetWeights ( array ( 100, 1 ) );
	    $this->sphinx->SetMatchMode ( $this->options['mode'] );
	    if (count($this->options['filtervals'])) { $this->sphinx->SetFilter ( $this->options['filter'], $this->options['filtervals'] ); }
	    if ($this->options['groupby']) { $this->sphinx->SetGroupBy ( $this->options['groupby'], SPH_GROUPBY_ATTR, $this->options['groupsort'] ); }
	    if ($this->options['sortby']) {
	      $this->sphinx->SetSortMode ( SPH_SORT_EXTENDED, $this->options['sortby'] );
	      $this->sphinx->SetSortMode ( SPH_SORT_EXPR, $this->options['sortexpr'] );
	    }
	    $this->sphinx->SetGroupDistinct ( $this->options['distinct'] );
	    if ($this->options['select']) { $this->sphinx->SetSelect ( $this->options['select'] ); }
	    $this->sphinx->SetLimits ( $this->offset, $this->num2show?$this->num2show:$this->options['limit'], $this->options['max_limit'] );
	    $this->sphinx->SetRankingMode ( $this->options['ranker'] );

	    // invoke sphinx query
	    $_search_result = $this->sphinx->Query($_query_str, $this->options['index']);

	    // echo '<pre>'; var_dump($_search_result); echo '</pre>'; die();
	    if ($_search_result === false) {
	      $this->sphinx_error = true;
	      $this->query_error = $this->sphinx->GetLastError();
	      return false;
	    }

	    if (isset($_search_result['matches']) && is_array($_search_result['matches'])) {
	      $_matched_ids = '(';
	      foreach ($_search_result['matches'] as $_match) {
	    	  $_matched_ids .= $_match['id'].',';
	      }
	      // remove last comma
	      $_matched_ids = substr_replace($_matched_ids, '', -1);
	      $_matched_ids .= ')';
	      $_sql_criteria = "index.biblio_id IN $_matched_ids";

	      $this->num_rows = $_search_result['total_found'];
	      $this->query_time = $_search_result['time'];
	      $this->criteria = array('sql_criteria' => $_sql_criteria, 'searched_fields' => $_searched_fields);

	      return $this->criteria;
	    } else {
	      $this->sphinx_no_result = true;
	      return false;
	    }
    }
}
