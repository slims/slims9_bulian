<?php
/**
 * biblio_list class
 * Class for generating list of bibliographic records from MongoDB
 *
 * Copyright (C) 2013 Arie Nugraha (dicarve@yahoo.com)
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

/**
 * Work around for fetch_assoc method invoked in makeOutput method
 */
class biblio_mongodb_result {
  private $nosql_cursor = false;
	public function __construct(&$obj_nosql_cursor) {
    $this->nosql_cursor = $obj_nosql_cursor;
	}

  public function fetch_assoc() {
    return $this->nosql_cursor->getNext();
	}

  public function free_result() {
    return $this->nosql_cursor->reset();
	}
}

class biblio_list extends biblio_list_model
{
  protected $options = array();
	protected $Biblio = false;
	protected $offset = 0;
	protected $cursor = 0;
	public $current_page = 1;
	public $num2show = 1;

  /**
   * Class Constructor
   *
   * @param   object  $obj_db
   * @param   integer	$int_num_show
   */
  public function __construct($obj_db, $int_num_show) {
    parent::__construct($obj_db, $int_num_show);
	  if (!class_exists('MongoClient')) {
	    throw new Exception('PHP Mongodb extension library is not installed yet!');
	  } else {
	    $Mongo = new MongoClient();
			// select database
			$this->Biblio = $Mongo->slims->biblio;

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
   * Method to print out document records
   *
   * @param   object  $obj_db
   * @param   integer $int_num2show
   * @param   boolean $bool_return_output
   * @return  string
   */
  public function getDocumentList($bool_return_output = true) {
	  global $sysconf;
	  // start time
	  $_start = function_exists('microtime')?microtime(true):time();
	  // execute query
    $this->cursor = $this->Biblio->find($this->criteria['sql_criteria'])->limit($this->num2show);
    $this->cursor->skip($this->offset);
	  // get total number of rows from query
	  $this->num_rows = $this->cursor->count();
		$this->resultset = new biblio_mongodb_result($this->cursor);
	  // end time
	  $_end = function_exists('microtime')?microtime(true):time();
	  $this->query_time = round($_end-$_start, 5);

	  if ($bool_return_output) {
	    // return the html result
	    return $this->makeOutput();
	  }
  }


  /**
   * Set options
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
    $_last_field = 'title';
    $_field = 'title';
		$_field_str = '';
		$_mongo_boolean_top = false;
		// parse query
    $this->orig_query = $str_criteria;
    $_queries = simbio_tokenizeCQL($str_criteria, $this->searchable_fields, $this->stop_words, $this->queries_word_num_allowed);
    if (count($_queries) < 1) {
      return null;
    }
    // loop each query
	  // echo '<pre>'; var_dump($_queries); echo '</pre>';
		$_mongo_search = array();
    foreach ($_queries as $_num => $_query) {
      // field
      $_field = $_query['f'];
			$_q = trim($_query['q']);

      //  break the loop if we meet `cql_end` field
      if ($_field == 'cql_end') { continue; }
	    // if field is boolean
	    if ($_field == 'boolean') {
				if ($_query['b'] == '*') {
				  $_mongo_boolean_top ='$or';
				} else if ($_query['b'] == '-') {
          $_mongo_boolean_top ='$not';
				} else {
				  $_mongo_boolean_top = '$and';
				}
				continue;
	    }
			if ($_query['b'] == '*') {
			  $_mongo_boolean_field ='$or';
			} else if ($_query['b'] == '-') {
        $_mongo_boolean_field ='$not';
			} else {
			  $_mongo_boolean_field = '$and';
			}

      $_mongo_search[$_field][$_mongo_boolean_field][] = array($_field => array('$regex' => new MongoRegex('/.*'.$_q.'.*/i')));
		  $_searched_fields[$_field] = $_field;
    }

		// preproccess search criteria to match Mongodb arrays
		$_mongo_search_tmp = $_mongo_search;
		$_mongo_search = array();
    if ($_mongo_boolean_top) {
			foreach ($_mongo_search_tmp as $_search_field => $_search_criteria) {
        foreach ($_search_criteria as $_logic => $_search_expr) {
          $_mongo_search[$_mongo_boolean_top][][$_logic] = $_search_expr;
				}
			}
		} else {
			foreach ($_mongo_search_tmp as $_search_field => $_search_criteria) {
        foreach ($_search_criteria as $_logic => $_search_expr) {
          $_mongo_search[$_logic][] = $_search_expr;
				}
			}
		}
		unset($_mongo_search_tmp);

		// echo '<pre>'; var_dump($_mongo_search); echo '</pre>';
	  // check if query is empty
	  if (!$_mongo_search) {
	    $this->no_query = true;
	    $_mongo_search['biblio_id'] = array('$gt' => 0);
	  }

	  $this->criteria = array('sql_criteria' => $_mongo_search, 'searched_fields' => $_searched_fields);
	  return $this->criteria;
  }
}
