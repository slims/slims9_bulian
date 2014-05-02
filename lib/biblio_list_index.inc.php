<?php
/**
 * biblio_list class
 * Class for generating list of bibliographic records from index table
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
  /**
   * Class Constructor
   *
   * @param   object  $obj_db
   * @param   integer	$int_num_show
   */
  public function __construct($obj_db, $int_num_show)
  {
    parent::__construct($obj_db, $int_num_show);
  }


  /**
   * Method to compile SQL statement based on criteria
   *
   * @param   string  $str_criteria
   * @return  void
   */
  protected function compileSQL() {
    global $sysconf;
    // get page number from http get var
    if (!isset($_GET['page']) OR $_GET['page'] < 1){ $_page = 1; } else {
      $_page = (integer)$_GET['page'];
    }
    $this->current_page = $_page;

    // count the row offset
    if ($_page <= 1) { $_offset = 0; } else {
      $_offset = ($_page*$this->num2show) - $this->num2show;
    }

    // init sql string
    $_sql_str = 'SELECT SQL_CALC_FOUND_ROWS index.biblio_id, index.title, index.author, index.topic, index.image, index.isbn_issn, index.publisher, index.publish_place, index.publish_year, index.labels, index.input_date';

    // checking custom frontpage fields file
    $custom_frontpage_record_file = SB.$sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/custom_frontpage_record.inc.php';
    if (file_exists($custom_frontpage_record_file)) {
      include $custom_frontpage_record_file;
      $this->enable_custom_frontpage = true;
      $this->custom_fields = $custom_fields;
      foreach ($this->custom_fields as $_field => $_field_opts) {
        if ($_field_opts[0] == 1 && !in_array($_field, array('availability', 'isbn_issn'))) {
          $_sql_str .= ", index.$_field";
        }
      }
    }

    // additional SQL string
    $_add_sql_str = ' WHERE';
    // main search criteria
    if ($this->criteria) {
      $_add_sql_str .= ' ('.$this->criteria['sql_criteria'].') ';
    } else {
      $_add_sql_str .= ' `index`.`opac_hide`=0';
	  }
    // promoted flag
    // if ($this->only_promoted) { $_add_sql_str .= ' AND promoted=1'; }

    $_sql_str .= ' FROM `search_biblio` AS `index` '.$_add_sql_str.' ORDER BY `index`.`last_update` DESC LIMIT '.$_offset.', '.$this->num2show;
    // for debugging purpose only
    // echo "<div style=\"border: 1px solid navy; padding: 5px; color: navy; margin: 5px;\">$_sql_str</div>";
	  return $_sql_str;
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
    $_previous_field = '';
    $_boolean = '';
    // parse query
    $this->orig_query = $str_criteria;
    $_queries = simbio_tokenizeCQL($str_criteria, $this->searchable_fields, $this->stop_words, $this->queries_word_num_allowed);
    // var_dump($_queries);
    if (count($_queries) < 1) {
      return null;
    }
    // loop each query
	  	  // echo '<pre>'; var_dump($_queries); echo '</pre>';
    foreach ($_queries as $_num => $_query) {
      // field
      $_field = $_query['f'];
	    $_is_phrase = isset($_query['is_phrase']);
      //  break the loop if we meet `cql_end` field
      if ($_field == 'cql_end') { break; }
	  	// if field is boolean
	  	if ($_field == 'boolean') {
	  	  if ($_query['b'] == '*') { $_boolean = 'OR'; } else { $_boolean = 'AND'; }
	  	  continue;
	  	} else {
        if ($_boolean) {
          $_sql_criteria .= " $_boolean ";
        } else {
          if ($_query['b'] == '*') {
          $_sql_criteria .= " OR ";
          } else { $_sql_criteria .= " AND "; }
        }
        $_b = $_query['b'];
        $_q = @$this->obj_db->escape_string(trim($_query['q']));
        if (in_array($_field, array('title', 'author', 'subject', 'notes'))) {
          $_q = '+'.( $_is_phrase?'"'.$_q.'"':$_q );
          if (!$_is_phrase) {
            $_q = preg_replace('@\s+@i', ' +', $_q);
          }
        }
        $_boolean = '';
	  	}
			$_searched_word = str_replace(array('+', '-', '*'), '', $_q);
			$this->words[$_searched_word] = $_searched_word;
	    $_searched_fields = $_field;
      // for debugging purpose only
      // echo "<p>$_num. $_field -> $_boolean -> $_sql_criteria</p><p>&nbsp;</p>";

	    // check fields
      switch ($_field) {
        case 'author' :
	  	    if ($_b == '-') { $_sql_criteria .= " NOT (MATCH (index.author) AGAINST ('$_q' IN BOOLEAN MODE))";
	  	    } else { $_sql_criteria .= " (MATCH (index.author) AGAINST ('$_q' IN BOOLEAN MODE))"; }
          break;
        case 'subject' :
	  	    if ($_b == '-') { $_sql_criteria .= " NOT (MATCH (index.topic) AGAINST ('$_q' IN BOOLEAN MODE))";
	  	    } else { $_sql_criteria .= " (MATCH (index.topic) AGAINST ('$_q' IN BOOLEAN MODE))"; }
          break;
        case 'location' :
	  	    if (!$this->disable_item_data) {
	  	  	if ($_b == '-') { $_sql_criteria .= " NOT (MATCH (index.location) AGAINST ('$_q' IN BOOLEAN MODE))";
	  	  	} else { $_sql_criteria .= " (MATCH (index.location) AGAINST ('$_q' IN BOOLEAN MODE))"; }
	  	    } else {
	  	  	if ($_b == '-') { $_sql_criteria .= " index.node !='$_q'";
	  	  	} else { $_sql_criteria .= " index.node = '$_q'"; }
	  	    }
          break;
        case 'colltype' :
          if (!$this->disable_item_data) {
	  	  	if ($_b == '-') { $_sql_criteria .= " NOT (MATCH (index.collection_types) AGAINST ('$_q' IN BOOLEAN MODE))";
	  	  	} else { $_sql_criteria .= " MATCH (index.collection_types) AGAINST ('$_q' IN BOOLEAN MODE)"; }
          }
          break;
        case 'itemcode' :
          if (!$this->disable_item_data) {
	  	  	if ($_b == '-') { $_sql_criteria .= " NOT (MATCH (index.items) AGAINST ('$_q' IN BOOLEAN MODE))";
	  	  	} else { $_sql_criteria .= " MATCH (index.items) AGAINST ('$_q' IN BOOLEAN MODE)"; }
	  	    }
          break;
        case 'callnumber' :
          if ($_b == '-') { $_sql_criteria .= ' biblio.call_number NOT LIKE \''.$_q.'%\'';
          } else { $_sql_criteria .= ' index.call_number LIKE \''.$_q.'%\''; }
          break;
        case 'itemcallnumber' :
	  	    if (!$this->disable_item_data) {
	  	  	if ($_b == '-') { $_sql_criteria .= ' item.call_number NOT LIKE \''.$_q.'%\'';
	  	  	} else { $_sql_criteria .= ' item.call_number LIKE \''.$_q.'%\''; }
	  	    }
          break;
        case 'class' :
          if ($_b == '-') { $_sql_criteria .= ' index.classification NOT LIKE \''.$_q.'%\'';
          } else { $_sql_criteria .= ' index.classification LIKE \''.$_q.'%\''; }
          break;
        case 'isbn' :
          if ($_b == '-') { $_sql_criteria .= ' index.isbn_issn NOT LIKE \''.$_q.'%\'';
          } else { $_sql_criteria .= ' index.isbn_issn LIKE \''.$_q.'%\''; }
          break;
        case 'publisher' :
          if ($_b == '-') { $_sql_criteria .= " index.publisher!='$_q'";
          } else { $_sql_criteria .= " index.publisher LIKE '$_q%'"; }
          break;
        case 'publishyear' :
          if ($_b == '-') { $_sql_criteria .= ' index.publish_year!=\''.$_q.'\'';
          } else { $_sql_criteria .= ' index.publish_year LIKE \''.$_q.'\''; }
          break;
        case 'gmd' :
          if ($_b == '-') { $_sql_criteria .= " index.gmd!='$_q'";
          } else { $_sql_criteria .= " index.gmd='$_q'"; }
          break;
        case 'notes' :
          if ($_b == '-') {
          $_sql_criteria .= " NOT (MATCH (index.notes) AGAINST ('".$_q."' IN BOOLEAN MODE))";
          } else { $_sql_criteria .= " (MATCH (index.notes) AGAINST ('".$_q."' IN BOOLEAN MODE))"; }
          break;
        case 'opengroup' :
          $_sql_criteria .= "(";
          break;
        case 'closegroup' :
          $_sql_criteria .= ")";
          break;
        default :
          if ($_b == '-') { $_sql_criteria .= " NOT (MATCH (index.title, index.series_title) AGAINST ('$_q' IN BOOLEAN MODE))";
          } else { $_sql_criteria .= " (MATCH (index.title, index.series_title) AGAINST ('$_q' IN BOOLEAN MODE))"; }
          break;
      }
    }

    // remove boolean's logic symbol prefix and suffix
    $_sql_criteria = preg_replace('@^(AND|OR|NOT)\s*|\s+(AND|OR|NOT)$@i', '', trim($_sql_criteria));
    // below for debugging purpose only
    // echo "<div style=\"border: 1px solid #f00; padding: 5px; color: #f00; margin: 5px;\">$_sql_criteria</div>";

    $this->criteria = array('sql_criteria' => $_sql_criteria, 'searched_fields' => $_searched_fields);
    return $this->criteria;
  }
}
