<?php
/**
 * API class
 * A Collection of API static utility methods
 *
 * Copyright (C) 2016  Hendro Wicaksono (hendrowicaksono@gmail.com)
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

class api
{
  /**
   * Static Method to load collection/bibliography data from database
   *
   * @param   object  $obj_db
   * @param   integer  $biblio_id
   * @return  array or false
   */
  public static function biblio_load($obj_db, $biblio_id)
  {
    $_return = FALSE;
    $i = 0;
    $s_bib = 'SELECT mg.gmd_name, mp.publisher_name, ml.language_name, ';
    $s_bib .= 'ma.place_name, mf.frequency, ct.content_type, mt.media_type, ';
    $s_bib .= 'ca.carrier_type, b.* ';
    $s_bib .= 'FROM biblio AS b ';
    $s_bib .= 'LEFT JOIN mst_gmd AS mg ON b.gmd_id=mg.gmd_id ';
    $s_bib .= 'LEFT JOIN mst_publisher AS mp ON b.publisher_id=mp.publisher_id ';
    $s_bib .= 'LEFT JOIN mst_language AS ml ON b.language_id=ml.language_id ';
    $s_bib .= 'LEFT JOIN mst_place AS ma ON b.publish_place_id=ma.place_id ';
    $s_bib .= 'LEFT JOIN mst_frequency AS mf ON b.frequency_id=mf.frequency_id ';
    $s_bib .= 'LEFT JOIN mst_content_type AS ct ON b.content_type_id=ct.id ';
    $s_bib .= 'LEFT JOIN mst_media_type AS mt ON b.media_type_id=mt.id ';
    $s_bib .= 'LEFT JOIN mst_carrier_type AS ca ON b.carrier_type_id=ca.id ';
    $s_bib .= 'WHERE b.biblio_id=\''.$biblio_id.'\'';
    $s_bib .= '';
    $q_bib = $obj_db->query($s_bib);
    if (!$obj_db->errno) {
      while ($r_bib = $q_bib->fetch_assoc()) {
        $_return[$i]['biblio_id'] = $r_bib['biblio_id'];
        $_return[$i]['title'] = $r_bib['title'];
        $_return[$i]['gmd_name'] = $r_bib['gmd_name'];
        $_return[$i]['sor'] = $r_bib['sor'];
        $_return[$i]['edition'] = $r_bib['edition'];
        $_return[$i]['isbn_issn'] = $r_bib['isbn_issn'];
        $_return[$i]['publisher_name'] = $r_bib['publisher_name'];
        $_return[$i]['publish_year'] = $r_bib['publish_year'];
        $_return[$i]['collation'] = $r_bib['collation'];
        $_return[$i]['series_title'] = $r_bib['series_title'];
        $_return[$i]['call_number'] = $r_bib['call_number'];
        $_return[$i]['language_name'] = $r_bib['language_name'];
        $_return[$i]['source'] = $r_bib['source'];
        $_return[$i]['place_place'] = $r_bib['place_name'];
        $_return[$i]['classification'] = $r_bib['classification'];
        $_return[$i]['notes'] = $r_bib['notes'];
        $_return[$i]['image'] = $r_bib['image'];
        $_return[$i]['opac_hide'] = $r_bib['opac_hide'];
        $_return[$i]['promoted'] = $r_bib['promoted'];
        $_return[$i]['labels'] = $r_bib['labels'];
        $_return[$i]['frequency'] = $r_bib['frequency'];
        $_return[$i]['spec_detail_info'] = $r_bib['spec_detail_info'];
        $_return[$i]['content_type'] = $r_bib['content_type'];
        $_return[$i]['media_type'] = $r_bib['media_type'];
        $_return[$i]['carrier_type'] = $r_bib['carrier_type'];
        $_return[$i]['input_date'] = $r_bib['input_date'];
        $_return[$i]['last_update'] = $r_bib['last_update'];
        $_return[$i]['uid'] = $r_bib['uid'];
        #AUTHORS
        $_return[$i]['authors'] = NULL;
        $s_aut = 'SELECT an.author_name, an.authority_type ';
        $s_aut .= 'FROM biblio AS bi, biblio_author AS ba, mst_author AS an ';
        $s_aut .= 'WHERE bi.biblio_id=ba.biblio_id AND ba.author_id=an.author_id ';
        $s_aut .= 'AND bi.biblio_id='.$r_bib['biblio_id'];
        #debug $s_aut
        #$_return[$i]['authors_sql'] = $s_aut;
        $q_aut = $obj_db->query($s_aut);
        $_ca = 0;
        while ($r_aut = $q_aut->fetch_assoc()) {
          $_return[$i]['authors'][$_ca]['author_name'] = $r_aut['author_name'];
          $_return[$i]['authors'][$_ca]['authority_type'] = $r_aut['authority_type'];
          $_ca++;
        }
        #SUBJECT/TOPIC
        $_return[$i]['subjects'] = NULL;
        $s_sub = 'SELECT mt.topic, mt.topic_type ';
        $s_sub .= 'FROM biblio AS bi, biblio_topic AS bt, mst_topic AS mt ';
        $s_sub .= 'WHERE bi.biblio_id=bt.biblio_id AND bt.topic_id=mt.topic_id ';
        $s_sub .= 'AND bi.biblio_id='.$r_bib['biblio_id'];
        #debug $s_sub
        #$_return[$i]['subjects_sql'] = $s_sub;
        $q_sub = $obj_db->query($s_sub);
        $_ct = 0;
        while ($r_sub = $q_sub->fetch_assoc()) {
          $_return[$i]['subjects'][$_ct]['topic'] = $r_sub['topic'];
          $_return[$i]['subjects'][$_ct]['topic_type'] = $r_sub['topic_type'];
          $_ct++;
        }
        #ITEM/HOLDING
        $_return[$i]['items'] = NULL;
        $s_ite = 'SELECT * FROM item AS i ';
        $s_ite .= 'WHERE i.biblio_id='.$r_bib['biblio_id'];
        #debug $s_ite
        #$_return[$i]['items_sql'] = $s_ite;
        $q_ite = $obj_db->query($s_ite);
        $_ci = 0;
        while ($r_ite = $q_ite->fetch_assoc()) {
          $_return[$i]['items'][$_ci]['item_code'] = $r_ite['item_code'];
          $_return[$i]['items'][$_ci]['inventory_code'] = $r_ite['inventory_code'];
          $_ci++;
        }

        $i++;
      }
    }
    return $_return;

  }

  /**
   * Static Method to write biblio activities logs
   *
   * @param   object  $obj_db
   * @param   integer  $biblio_id
   * @param   integer  $user_id
   * @param   string  $username
   * @param   string  $realname
   * @param   string  $title
   * @param   string  $action
   * @param   string  $affectedrow
   * @param   array  $rawdata
   * @return  void
   */
  public static function bibliolog_write($obj_db, $biblio_id, $user_id, $username, $realname, $title, $action, $affectedrow, $rawdata)
  {
    if (!$obj_db->error) {
      // log table
      $_log_table = 'biblio_log';
      // filter input
      $_biblio_id = (int) $obj_db->escape_string(trim($biblio_id));
      $_user_id = (int) $obj_db->escape_string(trim($user_id));
      $_username = $obj_db->escape_string(trim($username));
      $_realname = $obj_db->escape_string(trim($realname));
      $_title = $obj_db->escape_string(trim($title));
      $_ip = $_SERVER['REMOTE_ADDR'];
      if ($action === 'create') {
        $_action = 'create';
      } elseif ($action === 'edit') {
        $_action = 'edit';
      } elseif ($action === 'delete') {
        $_action = 'delete';
      } else {
        $_action = 'create';        
      }
      if ($affectedrow === 'description') {
        $_affectedrow = 'description';
      } elseif ($affectedrow === 'classification') {
        $_affectedrow = 'classification';
      } elseif ($affectedrow === 'subject') {
        $_affectedrow = 'subject';
      } elseif ($affectedrow === 'abstract') {
        $_affectedrow = 'abstract';
      } elseif ($affectedrow === 'cover') {
        $_affectedrow = 'cover';
      } else {
        $_affectedrow = 'description';     
      }
      $_rawdata = urlencode(serialize($rawdata));
      $_date = date('Y-m-d H:i:s');
      // insert log data to database
      @$obj_db->query('INSERT INTO '.$_log_table.'
        VALUES (NULL, \''.$_biblio_id.'\', \''.$_user_id.'\', \''.$_username.'\', \''.$_realname.'\', \''.$_title.'\', \''.$_ip.'\', \''.$_action.'\', \''.$_affectedrow.'\', \''.$_rawdata.'\', \''.$_date.'\')');
    }
  }


}
