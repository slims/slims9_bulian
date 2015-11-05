<?php
/**
 * biblio_list model
 * Template/Abstract Class for bibliographic records listing
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

abstract class biblio_list_model
{
  /* Public properties */
  public $num_rows = 0;
  public $num2show = 10;
  public $xml_detail = true;
  public $xml_result = true;
  public $only_promoted = false;
  public $show_labels = true;
  public $stop_words = array('a', 'an', 'of', 'the', 'to', 'so', 'as', 'be');
  public $query_time = 0;
  public $disable_item_data = false;
  public $enable_mark = true;
  public $query_error;
  public $current_page = 1;
  public $item_availability_message = 'none copy available';
  public $words = array();
  /* Protected properties */
  protected $obj_db = false;
  protected $resultset = false;
  protected $subquery = array();
  protected $biblio_ids = array();
  protected $emulate_short_word_search = false;
  protected $queries_word_num_allowed = 20;
  protected $criteria = array();
  protected $label_cache = array();
  protected $custom_fields = array();
  protected $enable_custom_frontpage = false;
  protected $orig_query;
  protected $searchable_fields = array('title', 'author', 'subject', 'isbn',
	   'gmd', 'colltype', 'class', 'callnumber', 'notes',
	   'publisher', 'publish_year', 'itemcode', 'location');
  protected $field_join_type = array();

  /**
   * Class Constructor
   *
   * @param   object  $obj_db
   * @param   integer	$int_num_show
   */
  public function __construct($obj_db, $int_num_show = 20) {
	  $this->obj_db = $obj_db;
	  $this->num2show = $int_num_show;
  }


  /**
   * Method to set search criteria
   * Extend this method
   *
   * @param   string  $str_criteria
   * @return  void
   */
  public function setSQLcriteria($str_criteria) { }


  /**
   * Method to compile SQL statement based on criteria
   *
   * @param   string  $str_criteria
   * @return  void
   */
  protected function compileSQL() { }


  /**
   * Method to get string of authors data of bibliographic record
   *
   * @param   object	$obj_db
   * @param   integer	$int_biblio_id
   * @param   boolean	$bool_return_array
   * @return  mixed
   */
  public static function getAuthors($obj_db, $int_biblio_id, $bool_return_array = false) {
	$_authors = '';
	$_authors_arr = array();
	$_sql_str = 'SELECT a.author_name, a.author_id FROM biblio_author AS ba
      LEFT JOIN biblio AS b ON ba.biblio_id=b.biblio_id
      LEFT JOIN mst_author AS a ON ba.author_id=a.author_id WHERE ba.biblio_id='.$int_biblio_id;
    // query the author
    $_author_q = $obj_db->query($_sql_str);
    // concat author data
    while ($_author_d = $_author_q->fetch_row()) {
      $counter = count ($_author_d);
      $_authors .= $_author_d[0];
	  if ($bool_return_array) {
		$_authors_arr[] = $_author_d[0];
	  }
      $_authors .= ' - ';
    }
	if ($bool_return_array) {
	  return $_authors_arr;
	} else {
	  return $_authors;
	}
  }


  /**
   * Method to get list of document IDs of result
   *
   * @return  mixed
   */
  public function getDocumentIds() {
    $_temp_resultset = $this->resultset;
    while ($_biblio_d = $_temp_resultset->fetch_assoc()) { $this->biblio_ids[] = $_biblio_d['biblio_id']; }
    unset($_temp_resultset);
    return $this->biblio_ids;
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
    $_sql_str = $this->compileSQL();
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
    if ($bool_return_output) {
      // return the html result
      return $this->makeOutput();
    }
  }


  /**
   * Method to make an output of document records
   *
   * @return  string
   */
  protected function makeOutput() {
    global $sysconf;
    // init the result buffer
    $_buffer = '';
    // keywords from last search
    $_keywords = '';
    // loop data
    $_i = 0;
    if (!$this->resultset) {
      return '<div class="errorBox">Query error : '.$this->query_error.'</div>';
    }

    if (isset($_GET['keywords'])) {
      $_keywords = urlencode(trim(urldecode($_GET['keywords'])));
    }
    // include biblio list HTML template callback
    include SB.$sysconf['template']['dir'].'/'.$sysconf['template']['theme'].'/biblio_list_template.php';
    $settings = '';
    $settings = get_object_vars($this);
    $settings['keywords'] = $_keywords;
    while ($_biblio_d = $this->resultset->fetch_assoc()) {
      $_buffer .= biblio_list_format($this->obj_db, $_biblio_d, $_i, $settings, $return_back);
      $_i++;
    }

    // free resultset memory
    $this->resultset->free_result();

    // paging
    if (($this->num_rows > $this->num2show)) {
      $_paging = '<div class="biblioPaging">'.simbio_paging::paging($this->num_rows, $this->num2show, 5).'</div>';
    } else {
      $_paging = '';
    }

    $_biblio_list = '';
    $_is_member_logged_in = utility::isMemberLogin() && $this->enable_mark;
    if ($_paging) {
    	$_biblio_list .= $_paging;
    }
    if ($_is_member_logged_in) {
      $_submit = '<div class="biblioMarkFormAction"><input type="submit" name="markBiblio" value="'.__('Put marked selection into basket').'" /></div>';
      $_biblio_list .= '<form class="biblioMarkForm" method="post" action="index.php?p=member#biblioBasket">';
      $_biblio_list .= $_submit;
    }
    $_biblio_list .= $_buffer;
    if ($_is_member_logged_in) {
      $_biblio_list .= $_submit;
      $_biblio_list .= '</form>';
    }
    if ($_paging) {
      $_biblio_list .= $_paging;
    }
    return $_biblio_list;
  }



  /**
   * Method to make an output of document records in simple XML format
   *
   * @return  string
   */
  public function XMLresult() {
    global $sysconf;
    $mods_version = '3.3';
    // loop data
    $_buffer = '<modsCollection xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.loc.gov/mods/v3" xmlns:slims="http://slims.web.id" xsi:schemaLocation="http://www.loc.gov/mods/v3 http://www.loc.gov/standards/mods/v3/mods-3-3.xsd">'."\n";
    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(true);
    
    $xml->startElementNS('slims', 'resultInfo', null);
    $xml->startElementNS('slims', 'modsResultNum', null); $xml->writeCdata($this->num_rows); $xml->endElement();
    $xml->startElementNS('slims', 'modsResultPage', null); $xml->writeCdata($this->current_page); $xml->endElement();
    $xml->startElementNS('slims', 'modsResultShowed', null); $xml->writeCdata($this->num2show); $xml->endElement();
    $xml->endElement();
	
    while ($_biblio_d = $this->resultset->fetch_assoc()) {
      $xml->startElement('mods');
      $xml->writeAttribute('version', $mods_version);
      $xml->writeAttribute('id', $_biblio_d['biblio_id']);
      
      // parse title
      $_title_sub = '';
      if (stripos($_biblio_d['title'], ':') !== false) {
        $_title_main = trim(substr_replace($_biblio_d['title'], '', stripos($_biblio_d['title'], ':')+1));
        $_title_sub = trim(substr_replace($_biblio_d['title'], '', 0, stripos($_biblio_d['title'], ':')+1));
      } else {
        $_title_main = trim($_biblio_d['title']);
      }

      // parse title
      $_title_main = trim($_biblio_d['title']);
      $_title_sub = '';
      $_title_statement_resp = '';
      if (stripos($_biblio_d['title'], '/') !== false) {
          $_title_main = trim(substr_replace($_biblio_d['title'], '', stripos($_biblio_d['title'], '/')+1));
	  $_title_statement_resp = trim(substr_replace($_biblio_d['title'], '', 0, stripos($_biblio_d['title'], '/')+1));
      }
      if (stripos($_biblio_d['title'], ':') !== false) {
          $_title_main = trim(substr_replace($_biblio_d['title'], '', stripos($_biblio_d['title'], ':')+1));
          $_title_sub = trim(substr_replace($_biblio_d['title'], '', 0, stripos($_biblio_d['title'], ':')+1));
      }

      $xml->startElement('titleInfo');
      $xml->startElement('title');
      $xml->writeCData($_title_main);
      $xml->endElement();
      if ($_title_sub) {
          // $_xml_output .= '<subTitle><![CDATA['.$_title_sub.']]></subTitle>'."\n";
          $xml->startElement('subTitle');
          $xml->writeCData($_title_sub);
          $xml->endElement();
      }
      // $_xml_output .= '</titleInfo>'."\n";
      $xml->endElement();

      // get the authors data
      $_biblio_authors_q = $this->obj_db->query('SELECT a.*,ba.level FROM mst_author AS a'
        .' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id='.$_biblio_d['biblio_id']);
      while ($_auth_d = $_biblio_authors_q->fetch_assoc()) {
        // some rules to set name type in mods standard
        if ($sysconf['authority_type'][$_auth_d['authority_type']] == 'Personal Name') {
          $sysconf['authority_type'][$_auth_d['authority_type']] = 'personal';
        } elseif ($sysconf['authority_type'][$_auth_d['authority_type']] == 'Organizational Body') {
          $sysconf['authority_type'][$_auth_d['authority_type']] = 'corporate';
        } elseif ($sysconf['authority_type'][$_auth_d['authority_type']] == 'Conference') {
          $sysconf['authority_type'][$_auth_d['authority_type']] = 'conference';
        } else {
          $sysconf['authority_type'][$_auth_d['authority_type']] = 'personal';
        }
        $xml->startElement('name'); $xml->writeAttribute('type', $sysconf['authority_type'][$_auth_d['authority_type']]); $xml->writeAttribute('authority', $_auth_d['auth_list']);
        $xml->startElement('namePart'); $xml->writeCData($_auth_d['author_name']); $xml->endElement();
        $xml->startElement('role');
            $xml->startElement('roleTerm'); $xml->writeAttribute('type', 'text');
            $xml->writeCData($sysconf['authority_level'][$_auth_d['level']]);
            $xml->endElement();
        $xml->endElement();
        $xml->endElement();
      }
      
      $_biblio_authors_q->free_result();
      $xml->startElement('typeOfResource'); $xml->writeAttribute('collection', 'yes'); $xml->writeCData('mixed material'); $xml->endElement();
      $xml->startElement('identifier'); $xml->writeAttribute('type', 'isbn'); $xml->writeCData(str_replace(array('-', ' '), '', $_biblio_d['isbn_issn'])); $xml->endElement();

      // imprint/publication data
      $xml->startElement('originInfo');
      $xml->startElement('place');
          $xml->startElement('placeTerm'); $xml->writeAttribute('type', 'text'); $xml->writeCData($_biblio_d['publish_place']); $xml->endElement();
          $xml->startElement('publisher'); $xml->writeCData($_biblio_d['publisher']); $xml->endElement();
          $xml->startElement('dateIssued'); $xml->writeCData($_biblio_d['publish_year']); $xml->endElement();
      $xml->endElement();
      $xml->endElement();

      // images
      $_image = '';
      if (!empty($_biblio_d['image'])) {
        $_image = urlencode($_biblio_d['image']);
	$xml->startElementNS('slims', 'image', null); $xml->writeCdata($_image); $xml->endElement();
      }

      $xml->endElement(); // MODS
    }
    // free resultset memory
    $this->resultset->free_result();
    
    $_buffer .= $xml->outputMemory();
    $_buffer .= '</modsCollection>';

    return $_buffer;
  }


  /**
   * Method to make an output of document records in JSON-LD format
   *
   * @return  string
   */
  public function JSONLDresult() {
    global $sysconf;
    $jsonld['@context'] = 'http://schema.org';
    $jsonld['@type'] = 'Book';

    // loop data
    $jsonld['total_rows'] = $this->num_rows;
    $jsonld['page'] = $this->current_page;
    $jsonld['records_each_page'] = $this->num2show;
    $jsonld['@graph'] = array();
	while ($_biblio_d = $this->resultset->fetch_assoc()) {
      $record = array();
      $record['@id'] = 'http://'.$_SERVER['SERVER_NAME'].SWB.'index.php?p=show_detail&id='.$_biblio_d['biblio_id'];
      $record['name'] = trim($_biblio_d['title']);

      // get the authors data
      $_biblio_authors_q = $this->obj_db->query('SELECT a.*,ba.level FROM mst_author AS a'
        .' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id='.$_biblio_d['biblio_id']);
	  $record['author'] = array();
      while ($_auth_d = $_biblio_authors_q->fetch_assoc()) {
		$record['author']['name'][] = trim($_auth_d['author_name']);
      }
      $_biblio_authors_q->free_result();

	  // ISBN
	  $record['isbn'] = $_biblio_d['isbn_issn'];

	  // publisher
	  $record['publisher'] = $_biblio_d['publisher'];

	  // publish date
	  $record['dateCreated'] = $_biblio_d['publish_year'];

			// doc images
      $_image = '';
      if (!empty($_biblio_d['image'])) {
        $_image = urlencode($_biblio_d['image']);
		$record['image'] = $_image;
      }

	  $jsonld['@graph'][] = $record;
    }

    // free resultset memory
    $this->resultset->free_result();

    return str_ireplace('\/', '/', json_encode($jsonld));
  }



  /**
   * Method to make an output of document records in simple XML format
   *
   * @return  string
   */
  public function RSSresult() {
    global $sysconf;
    // loop data
    $_buffer = '<rss version="2.0">'."\n";
    $_buffer .= '<channel>'."\n";
    $_buffer .= '<title><![CDATA[Collection of '.$sysconf['library_name'].']]></title>'."\n";
    $_buffer .= '<link><![CDATA[http://'.$_SERVER['SERVER_NAME'].SWB.']]></link>'."\n";
    $_buffer .= '<description><![CDATA[New collection of '.$sysconf['library_name'].']]></description>'."\n";
    $_buffer .= "\n";

    while ($_biblio_d = $this->resultset->fetch_assoc()) {
      $_buffer .= '<item>'."\n";
      $_buffer .= ' <title><![CDATA['.trim($_biblio_d['title']).']]></title>'."\n";
      $_buffer .= ' <link><![CDATA[http://'.$_SERVER['SERVER_NAME'].SWB.'/index.php?p=show_detail&id='.$_biblio_d['biblio_id'].']]></link>'."\n";
      $_buffer .= ' <pubDate><![CDATA['.date('D, d F Y H:i:s', strtotime($_biblio_d['input_date'])).']]></pubDate>'."\n";

      // get the authors data
      $_authors = $this->getAuthors($this->obj_db, $_biblio_d['biblio_id']);
			// remove last comma
      $_buffer .= ' <author><![CDATA['.$_authors.']]></author>'."\n";

      $_buffer .= '<description><![CDATA[Author: '.$_authors.' ISBN: '.$_biblio_d['isbn_issn'].']]></description>'."\n";
      $_buffer .= '</item>'."\n";
    }
    $_buffer .= '</channel>';
    $_buffer .= '</rss>';

    // free resultset memory
    $this->resultset->free_result();

    return $_buffer;
  }
}
