<?php
/**
 * Copyright (C) 2010  Wardiyono (wynerst@gmail.com), Arie Nugraha (dicarve@yahoo.com)
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
if (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

class biblio_indexer
{
	public $total_records = 0;
	public $indexed = 0;
	public $failed = array();
	public $errors = array();
	public $indexing_time = 0;
	private $exclude = array();
	private $obj_db = false;
	private $verbose = false;
	private $max_indexed = 1000000;
	private $index_type = 'search_biblio';

	public function __construct($obj_db, $bool_verbose = false) {
	    global $sysconf;
	    if ($sysconf['index']['type'] == 'mongodb') {
	    if (!class_exists('MongoClient')) {
	      throw new Exception('PHP Mongodb extension library is not installed yet!');
	    } else {
	      $this->index_type = 'nosql';
	      $Mongo = new MongoClient();
	        // select database
	    	$this->biblio = $Mongo->slims->biblio;
	      }
	    }
	    $this->obj_db = $obj_db;
	    $this->verbose = $bool_verbose;
	}


	/**
	 * Creating full index database of bibliographic records
	 * @param	boolean		$bool_empty_first: Emptying current index first
	 * @return	void
	 */
	public function createFullIndex($bool_empty_first = false) {
	  if ($bool_empty_first) {
	    $this->emptyingIndex();
	  }
	  $bib_sql = 'SELECT biblio_id FROM biblio ORDER BY biblio_id ASC LIMIT '.$this->max_indexed;
	  // query
	  $rec_bib = $this->obj_db->query($bib_sql);
	  $r = 0;
	  if ($rec_bib->num_rows > 0) {
            // start time counter
            $_start = function_exists('microtime')?microtime(true):time();
            $this->total_records = $rec_bib->num_rows;
            // loop records and create index
            while ($rb_id = $rec_bib->fetch_row()) {
              $biblio_id = $rb_id[0];
              $index = $this->makeIndex($biblio_id);
            }
            // get end time
            $_end = function_exists('microtime')?microtime(true):time();
            $this->indexing_time = $_end-$_start;
	  }
	}


	/**
	 * Emptying index table
	 * @return	boolean		true on success, false otherwise
	 */
	public function emptyingIndex() {
          @$this->obj_db->query('TRUNCATE TABLE `search_biblio`');
          if ($this->obj_db->errno) {
            $this->errors[] = $this->obj_db->error;
            return false;
          }
          return true;
	}


	/**
	 * Make index for one bibliographic record
	 * @param	int		$int_biblio_id: ID of biblio to index
	 * @return	boolean	false on Failed, true otherwise
	 */
	public function makeIndex($int_biblio_id) {
          $bib_sql = 'SELECT b.biblio_id, b.title, b.edition, b.publish_year, b.notes, b.series_title, b.classification, b.spec_detail_info,
            g.gmd_name AS `gmd`, pb.publisher_name AS `publisher`, pl.place_name AS `publish_place`, b.isbn_issn,
            lg.language_name AS `language`, b.call_number, b.opac_hide, b.promoted, b.labels, b.`collation`, b.image,
	    rct.content_type, rmt.media_type, rcrt.carrier_type,
	    b.input_date, b.last_update
            FROM biblio AS b
            LEFT JOIN mst_gmd AS g ON b.gmd_id = g.gmd_id
            LEFT JOIN mst_publisher AS pb ON b.publisher_id = pb.publisher_id
            LEFT JOIN mst_place AS pl ON b.publish_place_id = pl.place_id
            LEFT JOIN mst_language AS lg ON b.language_id = lg.language_id
            LEFT JOIN mst_content_type rct ON b.content_type_id=rct.id
            LEFT JOIN mst_media_type AS rmt ON b.media_type_id=rmt.id
            LEFT JOIN mst_carrier_type AS rcrt ON b.carrier_type_id=rcrt.id
	    WHERE b.biblio_id='.$int_biblio_id;
          // query
          $rec_bib = $this->obj_db->query($bib_sql);
          
          if ($rec_bib->num_rows < 1) {
          	return false;
          } else {
          	$rb_id = $rec_bib->fetch_assoc();
          }
          
          if ($this->verbose) { echo 'Indexing: '.$rb_id['title'].'...'; }
          $data['biblio_id'] = $int_biblio_id;
          
          /* GMD , Title, Year  */
          $data['title'] = $this->obj_db->escape_string($rb_id['title']);
          $data['edition'] = $this->obj_db->escape_string($rb_id['edition']);
          $data['gmd'] = $this->obj_db->escape_string($rb_id['gmd']);
          $data['content_type'] = $this->obj_db->escape_string($rb_id['content_type']);
          $data['media_type'] = $this->obj_db->escape_string($rb_id['media_type']);
          $data['carrier_type'] = $this->obj_db->escape_string($rb_id['carrier_type']);
          $data['publisher'] = $this->obj_db->escape_string($rb_id['publisher']);
          $data['publish_place'] = $this->obj_db->escape_string($rb_id['publish_place']);
          $data['isbn_issn'] = $this->obj_db->escape_string($rb_id['isbn_issn']);
          $data['language'] = $this->obj_db->escape_string($rb_id['language']);
          $data['publish_year'] = $rb_id['publish_year'];
          $data['classification'] = $this->obj_db->escape_string($rb_id['classification']);
          $data['spec_detail_info'] = $this->obj_db->escape_string($rb_id['spec_detail_info']);
          $data['call_number'] = $this->obj_db->escape_string($rb_id['call_number']);
          $data['opac_hide'] = $rb_id['opac_hide'];
          $data['promoted'] = $rb_id['promoted'];
          if ($rb_id['labels']) {
              $data['labels'] = $rb_id['labels'];
          } else {
              $data['labels'] = 'literal{NULL}';
          }
          $data['collation'] = $this->obj_db->escape_string($rb_id['collation']);
          $data['image'] = $this->obj_db->escape_string($rb_id['image']);
          $data['input_date'] = $rb_id['input_date'];
          $data['last_update'] = $rb_id['last_update'];
          if ($rb_id['notes'] != '') {
              $data['notes'] = trim($this->obj_db->escape_string(strip_tags($rb_id['notes'], '<br><p><div><span><i><em><strong><b><code>')));
          }
          if ($rb_id['series_title'] != '') {
              $data['series_title'] = $this->obj_db->escape_string($rb_id['series_title']);
          }
          
          /* author  */
          $au_all = '';
          $au_sql = 'SELECT ba.biblio_id, ba.level, au.author_name AS `name`, au.authority_type AS `type`
          	FROM biblio_author AS ba LEFT JOIN mst_author AS au ON ba.author_id = au.author_id
          	WHERE ba.biblio_id ='. $int_biblio_id;
          $au_id = $this->obj_db->query($au_sql);
          while($rs_au = $au_id->fetch_assoc()) {
          	$au_all .= $rs_au['name'] . ' - ';
          }
          if ($au_all !='') {
          	$au_all = substr_replace($au_all, '', -3);
          	$data['author'] = $this->obj_db->escape_string($au_all);
          }
          
          /* subject  */
          $topic_all = '';
          $topic_sql = 'SELECT bt.biblio_id, bt.level, tp.topic, tp.topic_type AS `type`
          	FROM biblio_topic AS bt LEFT JOIN mst_topic AS tp ON bt.topic_id = tp.topic_id
          	WHERE bt.biblio_id ='. $int_biblio_id;
          $topic_id = $this->obj_db->query($topic_sql);
          while ($rs_topic = $topic_id->fetch_assoc()) {
          	$topic_all .= $rs_topic['topic'] . ' - ';
          }
          if ($topic_all != '') {
          	$topic_all = substr_replace($topic_all, '', -3);
          	$data['topic'] = $this->obj_db->escape_string($topic_all);
          }
          
          /* items */
          $barcode_all = '';
          $barcode_sql = 'SELECT i.item_code FROM item AS i WHERE i.biblio_id ='. $int_biblio_id;
          $barcode_q = $this->obj_db->query($barcode_sql);
          while ($rs_barcode = $barcode_q->fetch_assoc()) {
          	$barcode_all .= $rs_barcode['item_code'] . ' - ';
          }
          if ($barcode_all != '') {
          	$barcode_all = substr_replace($barcode_all, '', -3);
          	$data['items'] = $barcode_all;
          }
          
          /* location  */
          $loc_all = '';
          $loc_sql = 'SELECT i.biblio_id, l.location_name AS `name`
          	FROM item AS i LEFT JOIN mst_location AS l ON i.location_id = l.location_id
          	WHERE i.biblio_id ='. $int_biblio_id;
          $loc_id = $this->obj_db->query($loc_sql);
          $_prev_loc = '';
          while ($rs_loc = $loc_id->fetch_assoc()) {
          	if ($rs_loc['name'] == $_prev_loc) { continue; }
          	$loc_all .= $rs_loc['name'] . ' - ';
          	$_prev_loc = $rs_loc['name'];
          }
          if ($loc_all != '') {
          	$loc_all = substr_replace($loc_all, '', -3);
          	$data['location'] = $this->obj_db->escape_string($loc_all);
          }
          
          /* collection types */
          $colltype_all = '';
          $colltype_sql = 'SELECT ct.coll_type_name AS `name`
          	FROM item AS i LEFT JOIN mst_coll_type AS ct ON i.coll_type_id = ct.coll_type_id
          	WHERE i.biblio_id ='. $int_biblio_id;
          $colltype_q = $this->obj_db->query($colltype_sql);
          $_prev_colltype = '';
          while ($rs_colltype = $colltype_q->fetch_assoc()) {
          	if ($rs_colltype['name'] == $_prev_colltype) { continue; }
          	$colltype_all .= $rs_colltype['name'] . ' - ';
          	$_prev_colltype = $rs_colltype['name'];
          }
          if ($colltype_all != '') {
          	$colltype_all = substr_replace($colltype_all, '', -3);
          	$data['collection_types'] = $this->obj_db->escape_string($colltype_all);
          }

          if ($this->index_type == 'nosql') {
            $this->biblio->insert($data);
	    // create index
	    $this->biblio->ensureIndex(array('title' => 1, 'author' => 1, 'topic' => 1));
	    if ($this->verbose) { echo " indexed\n"; }
	    $this->indexed++;
	  } else {
	    /*  SQL operation object  */
	    $sql_op = new simbio_dbop($this->obj_db);
	    /*  Insert all variable  */
	    if ($sql_op->insert('search_biblio', $data)) {
	    	if ($this->verbose) { echo " indexed\n"; }
	    	$this->indexed++;
	    } else {
	    	if ($this->verbose) { echo " FAILED! (Error: '.$sql_op->error.')\n"; }
	    	$this->failed[] = $int_biblio_id;
	    	// line below is for debugging purpose only
	    	// echo '<div>'.$sql_op->error.'</div>';
	    }
	  }

	  return true;
	}


	/**
	 * Update index
	 *
	 * @return	void
	 */
	public function updateFullIndex() {
	  $bib_sql = 'SELECT b.biblio_id FROM biblio AS b
	    LEFT JOIN search_biblio AS sb ON b.biblio_id = sb.biblio_id
	    WHERE sb.biblio_id is NULL ORDER BY b.biblio_id LIMIT '.$this->max_indexed;
	  // query
	  $rec_bib = $this->obj_db->query($bib_sql);
	  $r = 0;
	  if ($rec_bib->num_rows > 0) {
	    // start time counter
	    $_start = function_exists('microtime')?microtime(true):time();
	    $this->total_records = $rec_bib->num_rows;
	    while ($rb_id = $rec_bib->fetch_row()) {
	    	$biblio_id = $rb_id[0];
	    	$index = $this->makeIndex($biblio_id);
	    }
	    // end time
	    $_end = function_exists('microtime')?microtime(true):time();
	    $this->indexing_time = $_end-$_start;
	  }
	}
}
