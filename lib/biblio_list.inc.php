<?php
/**
 * biblio_list class
 * Class for generating list of bibliographic records
 *
 * Copyright (C) 2009 Arie Nugraha (dicarve@yahoo.com)
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

class biblio_list extends biblio_list_model {
  protected $searchable_fields = array('title', 'author', 'subject', 'isbn',
		'publisher', 'gmd', 'notes', 'colltype', 'publishyear',
		'location', 'itemcode', 'callnumber', 'itemcallnumber', 'notes');
  protected $field_join_type = array('title' => 'OR', 'author' => 'OR', 'subject' => 'OR');


  /**
   * Class Constructor
   *
   * @param   object  $obj_db
   * @param   integer	$int_num_show
   */
  public function __construct($obj_db, $int_num_show) {
    parent::__construct($obj_db, $int_num_show);
  }


  /**
   * Method to set search criteria
   *
   * @param   string  $str_criteria
   * @return  void
   */
  public function setSQLcriteria($str_criteria) {
    if (!$str_criteria)
      return null;
    // defaults
    $_sql_criteria = '';
    $_searched_fields = array();
    $_title_buffer = '';
    $_previous_field = '';
    $_boolean = '';
    // parse query
    $this->orig_query = $str_criteria;
    $_queries = simbio_tokenizeCQL($str_criteria, $this->searchable_fields, $this->stop_words, $this->queries_word_num_allowed);
    // echo '<pre>'; var_dump($_queries); echo '</pre>';
    if (count($_queries) < 1) {
      return null;
    }
    // loop each query
    foreach ($_queries as $_num => $_query) {
      // field
      $_field = $_query['f'];
      // for debugging purpose only
      // echo "<p>$_num. $_field -> $_boolean -> $_sql_criteria</p><p>&nbsp;</p>";
      // boolean
      if ($_title_buffer == '' && $_field != 'boolean') {
        $_sql_criteria .= " $_boolean ";
      }
      // $_sql_criteria .= " $_boolean ";
      // flush title string concatenation
      if ($_field != 'title' && $_title_buffer != '') {
        $_title_buffer = trim($_title_buffer);
        $_sql_criteria .= " biblio.biblio_id IN(SELECT DISTINCT biblio_id FROM biblio WHERE MATCH (title, series_title) AGAINST ('$_title_buffer' IN BOOLEAN MODE)) ";
        // reset title buffer
        $_title_buffer = '';
      }
      //  break the loop if we meet `cql_end` field
      if ($_field == 'cql_end') { break; }
      // boolean mode
      $_b = isset($_query['b'])?$_query['b']:$_query;
      if ($_b == '*') {
        $_boolean = 'OR';
      } else { $_boolean = 'AND'; }
      // search value
      $_q = @$this->obj_db->escape_string($_query['q']);
			$_searched_word = str_replace(array('+', '-', '*'), '', $_q);
			$this->words[$_searched_word] = $_searched_word;
      // searched fields flag set
      $_searched_fields[$_field] = 1;
      $_previous_field = $_field;
      // check field
      if ($_field == 'title') {
        if (strlen($_q) < 4) {
          $_previous_field = 'title_short';
          $_sql_criteria .= " biblio.title LIKE '%$_q%' ";
          $_title_buffer = '';
        } else {
          if (isset($_query['is_phrase'])) {
            $_title_buffer .= ' '.$_b.'"'.$_q.'"';
          } else {
            $_title_buffer .= ' '.$_b.$_q;
          }
        }
      } else if ($_field == 'author') {
        if ($_b == '-') {
          $_sql_criteria .= " biblio.biblio_id NOT IN(SELECT ba.biblio_id FROM biblio_author AS ba"
            ." LEFT JOIN mst_author AS a ON ba.author_id=a.author_id"
            ." WHERE author_name LIKE '%$_q%')";
        } else {
          $_sql_criteria .= " biblio.biblio_id IN(SELECT ba.biblio_id FROM biblio_author AS ba"
            ." LEFT JOIN mst_author AS a ON ba.author_id=a.author_id"
            ." WHERE author_name LIKE '%$_q%')";
        }
      } else if ($_field == 'subject') {
        if ($_b == '-') {
          $_sql_criteria .= " biblio.biblio_id NOT IN(SELECT bt.biblio_id FROM biblio_topic AS bt"
            ." LEFT JOIN mst_topic AS t ON bt.topic_id=t.topic_id"
            ." WHERE topic LIKE '%$_q%')";
        } else {
          $_sql_criteria .= " biblio.biblio_id IN(SELECT bt.biblio_id FROM biblio_topic AS bt"
            ." LEFT JOIN mst_topic AS t ON bt.topic_id=t.topic_id"
            ." WHERE topic LIKE '%$_q%')";
        }
        // reset title buffer
        $_title_buffer = '';
      } else {
        switch ($_field) {
          case 'location' :
			if (!$this->disable_item_data) {
			  $_subquery = 'SELECT location_id FROM mst_location WHERE location_name=\''.$_q.'\'';
			  if ($_b == '-') {
				  $_sql_criteria .= " item.location_id NOT IN ($_subquery)";
			  } else { $_sql_criteria .= " item.location_id IN ($_subquery)"; }
			} else {
			  if ($_b == '-') {
				  $_sql_criteria .= " biblio.node_id !='$_q'";
			  } else { $_sql_criteria .= " biblio.node_id = '$_q'"; }
			}
            break;
          case 'colltype' :
			if (!$this->disable_item_data) {
			  $_subquery = 'SELECT coll_type_id FROM mst_coll_type WHERE coll_type_name=\''.$_q.'\'';
			  if ($_b == '-') {
				  $_sql_criteria .= " item.coll_type_id NOT IN ($_subquery)";
			  } else { $_sql_criteria .= " item.coll_type_id IN ($_subquery)"; }
			}
            break;
          case 'itemcode' :
			if (!$this->disable_item_data) {
			  if ($_b == '-') {
				$_sql_criteria .= " item.item_code != '$_q'";
			  } else { $_sql_criteria .= " item.item_code LIKE '$_q%'"; }
			}
            break;
          case 'callnumber' :
            if ($_b == '-') {
              $_sql_criteria .= ' biblio.call_number NOT LIKE \''.$_q.'%\'';
            } else { $_sql_criteria .= ' biblio.call_number LIKE \''.$_q.'%\''; }
            break;
          case 'itemcallnumber' :
			if (!$this->disable_item_data) {
			  if ($_b == '-') {
				  $_sql_criteria .= ' item.call_number NOT LIKE \''.$_q.'%\'';
			  } else { $_sql_criteria .= ' item.call_number LIKE \''.$_q.'%\''; }
			}
            break;
          case 'class' :
            if ($_b == '-') {
              $_sql_criteria .= ' biblio.classification NOT LIKE \''.$_q.'%\'';
            } else { $_sql_criteria .= ' biblio.classification LIKE \''.$_q.'%\''; }
            break;
          case 'isbn' :
            if ($_b == '-') {
              $_sql_criteria .= ' biblio.isbn_issn!=\''.$_q.'\'';
            } else { $_sql_criteria .= ' biblio.isbn_issn=\''.$_q.'\''; }
            break;
          case 'publisher' :
            $_subquery = 'SELECT publisher_id FROM mst_publisher WHERE publisher_name LIKE \'%'.$_q.'%\'';
            if ($_b == '-') {
              $_sql_criteria .= " biblio.publisher_id NOT IN ($_subquery)";
            } else { $_sql_criteria .= " biblio.publisher_id IN ($_subquery)"; }
            break;
          case 'publishyear' :
            if ($_b == '-') {
              $_sql_criteria .= ' biblio.publish_year!=\''.$_q.'\'';
            } else { $_sql_criteria .= ' biblio.publish_year=\''.$_q.'\''; }
            break;
          case 'gmd' :
            $_subquery = 'SELECT gmd_id FROM mst_gmd WHERE gmd_name=\''.$_q.'\'';
            if ($_b == '-') {
              $_sql_criteria .= " biblio.gmd_id NOT IN ($_subquery)";
            } else { $_sql_criteria .= " biblio.gmd_id IN ($_subquery)"; }
            break;
          case 'notes' :
			      $_q = isset($_query['is_phrase'])?'"'.$_q.'"':$_q;
            if ($_b == '-') {
              $_sql_criteria .= " NOT (MATCH (biblio.notes) AGAINST ('".$_q."' IN BOOLEAN MODE))";
            } else { $_sql_criteria .= " (MATCH (biblio.notes) AGAINST ('".$_q."' IN BOOLEAN MODE))"; }
            break;
        }
      }
    }

    // remove boolean's logic symbol prefix and suffix
    $_sql_criteria = preg_replace('@^(AND|OR|NOT)\s*|\s+(AND|OR|NOT)$@i', '', trim($_sql_criteria));
    // below for debugging purpose only
    // echo "<div style=\"border: 1px solid #f00; padding: 5px; color: #f00; margin: 5px;\">$_sql_criteria</div>";

    $this->criteria = array('sql_criteria' => $_sql_criteria, 'searched_fields' => $_searched_fields);
    return $this->criteria;
  }


  /**
   * Method to print out document records
   *
   * @param   object  $obj_db
   * @param   integer $int_num2show
   * @param   boolean $bool_return_output
   * @return  string
   */
  public function compileSQL() {
    global $sysconf;
    // get page number from http get var
    if (!isset($_GET['page']) OR $_GET['page'] < 1){
      $_page = 1;
    } else{
      $_page = (integer)$_GET['page'];
    }
    $this->current_page = $_page;

    // count the row offset
    if ($_page <= 1) {
      $_offset = 0;
    } else {
      $_offset = ($_page*$this->num2show) - $this->num2show;
    }

    // init sql string
    $_sql_str = 'SELECT SQL_CALC_FOUND_ROWS biblio.biblio_id, biblio.title, biblio.image, biblio.isbn_issn,
			biblio.publish_year, pbl.publisher_name AS `publisher`, pplc.place_name AS `publish_place`, biblio.labels, biblio.input_date';

    // checking custom frontpage fields file
    $custom_frontpage_record_file = SB.$sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/custom_frontpage_record.inc.php';
    if (file_exists($custom_frontpage_record_file)) {
      include $custom_frontpage_record_file;
      $this->enable_custom_frontpage = true;
      $this->custom_fields = $custom_fields;
      foreach ($this->custom_fields as $_field => $_field_opts) {
        if ($_field_opts[0] == 1 && !in_array($_field, array('availability', 'isbn_issn'))) {
          $_sql_str .= ", biblio.$_field";
        }
      }
    }

    // additional SQL string
    $_add_sql_str = ' LEFT JOIN mst_publisher AS pbl ON biblio.publisher_id=pbl.publisher_id ';
    $_add_sql_str .= ' LEFT JOIN mst_place AS pplc ON biblio.publish_place_id=pplc.place_id ';

    // location
    if ($this->criteria) {
      if (isset($this->criteria['searched_fields']['location']) || isset($this->criteria['searched_fields']['colltype'])) {
        if (!$this->disable_item_data) {
		  $_add_sql_str .= ' LEFT JOIN item ON biblio.biblio_id=item.biblio_id ';
		}
      }
    }

    $_add_sql_str .= ' WHERE opac_hide=0 ';
    // promoted flag
    // if ($this->only_promoted) { $_add_sql_str .= ' AND promoted=1'; }
    // main search criteria
    if ($this->criteria) {
      $_add_sql_str .= ' AND ('.$this->criteria['sql_criteria'].') ';
    }

    $_sql_str .= ' FROM biblio '.$_add_sql_str.' ORDER BY biblio.last_update DESC LIMIT '.$_offset.', '.$this->num2show;
    // for debugging purpose only
    // echo "<div style=\"border: 1px solid navy; padding: 5px; color: navy; margin: 5px;\">$_sql_str</div>";
	  return $_sql_str;
  }
}
