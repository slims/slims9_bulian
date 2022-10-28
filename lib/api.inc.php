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
  #Bibliographic modules related
  /**
   * Static Method to load collection/bibliography data from database
   *
   * @param   object  $obj_db
   * @param   integer  $biblio_id
   * @return  array or false
   */
  public static function biblio_load($obj_db, $biblio_id)
  {
    global $sysconf;
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
      $_return = [];
      while ($r_bib = $q_bib->fetch_assoc()) {
        $_return['id'] = $r_bib['biblio_id'];
        $_return['_id'] = $r_bib['biblio_id'];
        $_return['biblio_id'] = $r_bib['biblio_id'];
        $_return['title'] = $r_bib['title'];
        $_return['gmd_name'] = $r_bib['gmd_name'];
        $_return['sor'] = $r_bib['sor'];
        $_return['edition'] = $r_bib['edition'];
        $_return['isbn_issn'] = $r_bib['isbn_issn'];
        $_return['publisher_name'] = $r_bib['publisher_name'];
        $_return['publish_year'] = $r_bib['publish_year'];
        $_return['collation'] = $r_bib['collation'];
        $_return['series_title'] = $r_bib['series_title'];
        $_return['call_number'] = $r_bib['call_number'];
        $_return['language_name'] = $r_bib['language_name'];
        $_return['source'] = $r_bib['source'];
        $_return['place'] = $r_bib['place_name'];
        $_return['classification'] = $r_bib['classification'];
        $_return['notes'] = $r_bib['notes'];
        $_return['image'] = $r_bib['image'];
        $_return['opac_hide'] = $r_bib['opac_hide'];
        $_return['promoted'] = $r_bib['promoted'];
        $_return['labels'] = $r_bib['labels'];
        $_return['frequency'] = $r_bib['frequency'];
        $_return['spec_detail_info'] = $r_bib['spec_detail_info'];
        $_return['content_type'] = $r_bib['content_type'];
        $_return['media_type'] = $r_bib['media_type'];
        $_return['carrier_type'] = $r_bib['carrier_type'];
        #$_return['input_date'] = $r_bib['input_date'];
        #$_return['last_update'] = $r_bib['last_update'];
        $_return['uid'] = $r_bib['uid'];
        #AUTHORS
        $_return['authors'] = NULL;
        $s_aut = 'SELECT an.author_name, an.authority_type, ba.level ';
        $s_aut .= 'FROM biblio AS bi, biblio_author AS ba, mst_author AS an ';
        $s_aut .= 'WHERE bi.biblio_id=ba.biblio_id AND ba.author_id=an.author_id ';
        $s_aut .= 'AND bi.biblio_id='.$r_bib['biblio_id'].' ';
        $s_aut .= 'ORDER BY an.author_id ASC ';
        #debug $s_aut
        #$_return['authors_sql'] = $s_aut;
        $q_aut = $obj_db->query($s_aut);
        $_ca = 0;
        while ($r_aut = $q_aut->fetch_assoc()) {
          $_return['authors'][$_ca]['author_name'] = $r_aut['author_name'];
          $_type = $r_aut['authority_type'];
          $_return['authors'][$_ca]['authority_type'] = $sysconf['authority_type'][$_type];
          #$_return['authors'][$_ca]['authority_level'] = $r_aut['level'];
          $_level = $r_aut['level'];
          $_return['authors'][$_ca]['authority_level'] = $sysconf['authority_level'][$_level];
          $_ca++;
        }
        #SUBJECT/TOPIC
        $_return['subjects'] = NULL;
        $s_sub = 'SELECT mt.topic, mt.topic_type, bt.level ';
        $s_sub .= 'FROM biblio AS bi, biblio_topic AS bt, mst_topic AS mt ';
        $s_sub .= 'WHERE bi.biblio_id=bt.biblio_id AND bt.topic_id=mt.topic_id ';
        $s_sub .= 'AND bi.biblio_id='.$r_bib['biblio_id'].' ';
        $s_sub .= 'ORDER BY mt.topic_id ASC ';
        #debug $s_sub
        #$_return['subjects_sql'] = $s_sub;
        $q_sub = $obj_db->query($s_sub);
        $_ct = 0;
        while ($r_sub = $q_sub->fetch_assoc()) {
          $_return['subjects'][$_ct]['topic'] = $r_sub['topic'];
          $_type = $r_sub['topic_type'];
          $_return['subjects'][$_ct]['topic_type'] = $r_sub['topic_type'];
          $_return['subjects'][$_ct]['topic_type'] = $sysconf['subject_type'][$_type];
          $_level = $r_sub['level'];
          if ($_level == '1') {
            $_return['subjects'][$_ct]['topic_level'] = 'Primary';
          } elseif ($_level == '2') {
            $_return['subjects'][$_ct]['topic_level'] = 'Additional';
          } else {
            $_return['subjects'][$_ct]['topic_level'] = null;
          }
          $_ct++;
        }
        #ITEM/HOLDING
        $_return['items'] = NULL;
        $s_ite = 'SELECT i.*, ct.*, loc.*, mis.*, msp.* ';
        $s_ite .= 'FROM item AS i ';
        $s_ite .= 'LEFT JOIN ';
        $s_ite .= 'mst_coll_type AS ct ';
        $s_ite .= 'ON i.coll_type_id=ct.coll_type_id ';
        $s_ite .= 'LEFT JOIN ';
        $s_ite .= 'mst_location AS loc ';
        $s_ite .= 'ON i.location_id=loc.location_id ';
        $s_ite .= 'LEFT JOIN ';
        $s_ite .= 'mst_item_status AS mis ';
        $s_ite .= 'ON i.item_status_id=mis.item_status_id ';
        $s_ite .= 'LEFT JOIN ';
        $s_ite .= 'mst_supplier AS msp ';
        $s_ite .= 'ON i.supplier_id=msp.supplier_id ';
        $s_ite .= 'WHERE ';
        $s_ite .= 'biblio_id=\''.$r_bib['biblio_id'].'\' ';
        $s_ite .= 'ORDER BY i.item_id ASC ';

        #debug $s_ite
        #$_return['items_sql'] = $s_ite;
        $q_ite = $obj_db->query($s_ite);
        $_ci = 0;
        while ($r_ite = $q_ite->fetch_assoc()) {
          $_return['items'][$_ci]['item_id'] = $r_ite['item_id'];
          $_return['items'][$_ci]['item_code'] = $r_ite['item_code'];
          $_return['items'][$_ci]['call_number'] = $r_ite['call_number'];
          $_return['items'][$_ci]['coll_type_name'] = $r_ite['coll_type_name'];
          $_return['items'][$_ci]['shelf_location'] = $r_ite['site'];
          $_return['items'][$_ci]['location_name'] = $r_ite['location_name'];
          $_return['items'][$_ci]['inventory_code'] = $r_ite['inventory_code'];
          if (is_null($r_ite['item_status_name'])) {
            $_return['items'][$_ci]['item_status'] = 'Available';
          } else {
            $_return['items'][$_ci]['item_status'] = $r_ite['item_status_name'];
          }
          $_return['items'][$_ci]['order_no'] = $r_ite['order_no'];
          $_return['items'][$_ci]['order_date'] = $r_ite['order_date'];
          $_return['items'][$_ci]['received_date'] = $r_ite['received_date'];
          $_return['items'][$_ci]['supplier_name'] = $r_ite['supplier_name'];
          $_source = $r_ite['source'];
          if ($_source == '1') {
            $_return['items'][$_ci]['source'] = 'Buy';
          } elseif ($_source == '2') {
            $_return['items'][$_ci]['source'] = 'Prize/Grant';
          }
          $_return['items'][$_ci]['invoice'] = $r_ite['invoice'];
          $_return['items'][$_ci]['invoice_date'] = $r_ite['invoice_date'];
          $_return['items'][$_ci]['price'] = $r_ite['price'];
          $_return['items'][$_ci]['price_currency'] = $r_ite['price_currency'];
          $_return['items'][$_ci]['input_date'] = $r_ite['input_date'];
          $_return['items'][$_ci]['last_update'] = $r_ite['last_update'];
          $_return['items'][$_ci]['uid'] = $r_ite['uid'];
          $_ci++;
        }
        $_return['hash']['biblio'] = sha1(urlencode(serialize($_return)));
        $_return['hash']['classification'] = sha1(urlencode(serialize($_return['classification'])));
        $_return['hash']['authors'] = sha1(urlencode(serialize($_return['authors'])));
        $_return['hash']['subjects'] = sha1(urlencode(serialize($_return['subjects'])));
        $_return['hash']['image'] = sha1(urlencode(serialize($_return['image'])));
        $_return['input_date'] = $r_bib['input_date'];
        $_return['last_update'] = $r_bib['last_update'];
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
  public static function bibliolog_write($obj_db, $biblio_id, $user_id, $realname, $title, $action, $affectedrow, $rawdata, $additional_information = NULL)
  {
    if (!$obj_db->error) {
      // log table
      $_log_table = 'biblio_log';
      // filter input
      $_biblio_id = (int) $obj_db->escape_string(trim($biblio_id));
      $_user_id = (int) $obj_db->escape_string(trim($user_id));
      $_realname = $obj_db->escape_string(trim($realname));
      $_title = $obj_db->escape_string(trim($title));
      $_ip = $_SERVER['REMOTE_ADDR'];
      if ($action === 'create') {
        $_action = 'create';
      } elseif ($action === 'update') {
        $_action = 'update';
      } elseif ($action === 'delete') {
        $_action = 'delete';
      } else {
        $_action = 'create';        
      }
      if ($affectedrow === 'description') {
        $_affectedrow = 'description';
      } elseif ($affectedrow === 'classification') {
        $_affectedrow = 'classification';
      } elseif ($affectedrow === 'author') {
        $_affectedrow = 'author';
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
      $_additional_information = $obj_db->escape_string(trim($additional_information));
      $_date = date('Y-m-d H:i:s');
      // insert log data to database
      @$obj_db->query('INSERT INTO '.$_log_table.'
        VALUES (NULL, \''.$_biblio_id.'\', \''.$_user_id.'\', \''.$_realname.'\', \''.$_title.'\', \''.$_ip.'\', \''.$_action.'\', \''.$_affectedrow.'\', \''.$_rawdata.'\', \''.$_additional_information.'\', \''.$_date.'\')');
    }
  }

  public static function bibliolog_compare($obj_db, $biblio_id, $user_id, $realname, $title, $current, $previous = NULL)
  {
    if ($previous == NULL) {
      if ($current['classification'] != 'NONE') {
        api::bibliolog_write($obj_db, $biblio_id, $user_id, $realname, $title, 'update', 'classification', $current, 'New data. Classification. Number: '.$current['classification']);
      }
      if ($current['image'] != NULL) {
        api::bibliolog_write($obj_db, $biblio_id, $user_id, $realname, $title, 'update', 'cover', $current, 'New data. Image. File: '.$current['image']);
      }
      if ($current['authors'] != NULL) {
        $_authors = '';
        foreach ($current['authors'] as $key => $value) {
          $_authors .= $value['author_name'].'; ';
        }
        api::bibliolog_write($obj_db, $biblio_id, $user_id, $realname, $title, 'update', 'author', $current, 'New data. Author. Names: '.$_authors);
      }
      if ($current['subjects'] != NULL) {
        $_subjects = '';
        foreach ($current['subjects'] as $key => $value) {
          $_subjects .= $value['topic'].'; ';
        }
        api::bibliolog_write($obj_db, $biblio_id, $user_id, $realname, $title, 'update', 'subject', $current, 'New data. Subject. Names: '.$_subjects);
      }
    } else {
      if ($current['hash']['biblio'] != $previous['hash']['biblio']) {
        api::bibliolog_write($obj_db, $biblio_id, $user_id, $realname, $title, 'update', 'description', $current, 'Updated data. Bibliography.');
      }
      if ( ($current['classification'] != 'NONE') AND ($current['classification'] != $previous['classification']) ) {
        api::bibliolog_write($obj_db, $biblio_id, $user_id, $realname, $title, 'update', 'classification', $current, 'Updated data. Classification. Number: '.$current['classification']);
      }
      if ( ($current['image'] != NULL) AND ($current['image'] != $previous['image']) ) {
        api::bibliolog_write($obj_db, $biblio_id, $user_id, $realname, $title, 'update', 'cover', $current, 'Updated data. Image. File: '.$current['image']);
      }
      if ( ($current['authors'] != NULL) AND ($current['hash']['authors'] != $previous['hash']['authors']) ) {
        $_authors = '';
        foreach ($current['authors'] as $key => $value) {
          $_authors .= $value['author_name'].'; ';
        }
        api::bibliolog_write($obj_db, $biblio_id, $user_id, $realname, $title, 'update', 'author', $current, 'Updated data. Author. Names: '.$_authors);
      }
      if ( ($current['subjects'] != NULL) AND ($current['hash']['subjects'] != $previous['hash']['subjects']) ) {
        $_subjects = '';
        foreach ($current['subjects'] as $key => $value) {
          $_subjects .= $value['topic'].'; ';
        }
        api::bibliolog_write($obj_db, $biblio_id, $user_id, $realname, $title, 'update', 'subject', $current, 'Updated data. Subject. Names: '.$_subjects);
      }

    }
  }

  #Membership modules related
  public static function member_load($obj_db, $member_id)
  {
    $_return = FALSE;
    $i = 0;
    $s_mbr = '';
    $s_mbr .= 'SELECT mmt.member_type_name, mbr.* ';
    $s_mbr .= 'FROM member AS mbr ';
    $s_mbr .= 'LEFT JOIN mst_member_type AS mmt ON mbr.member_type_id=mmt.member_type_id ';
    $s_mbr .= 'WHERE mbr.member_id=\''.$member_id.'\'';
    $q_mbr = $obj_db->query($s_mbr);
    if (!$obj_db->errno) {
      $_return = [];
      while ($r_mbr = $q_mbr->fetch_assoc()) {
        $_return[$i]['member_id'] = $r_mbr['member_id'];
        $_return[$i]['member_name'] = $r_mbr['member_name'];
        $_return[$i]['gender'] = $r_mbr['gender'];
        $_return[$i]['birth_date'] = $r_mbr['birth_date'];
        $_return[$i]['member_type_name'] = $r_mbr['member_type_name'];
        $_return[$i]['member_address'] = $r_mbr['member_address'];
        $_return[$i]['member_mail_address'] = $r_mbr['member_mail_address'];
        $_return[$i]['member_email'] = $r_mbr['member_email'];
        $_return[$i]['postal_code'] = $r_mbr['postal_code'];
        $_return[$i]['inst_name'] = $r_mbr['inst_name'];
        $_return[$i]['is_new'] = $r_mbr['is_new'];
        $_return[$i]['member_image'] = $r_mbr['member_image'];
        $_return[$i]['pin'] = $r_mbr['pin'];
        $_return[$i]['member_phone'] = $r_mbr['member_phone'];
        $_return[$i]['member_fax'] = $r_mbr['member_fax'];
        $_return[$i]['member_since_date'] = $r_mbr['member_since_date'];
        $_return[$i]['register_date'] = $r_mbr['register_date'];
        $_return[$i]['expire_date'] = $r_mbr['expire_date'];
        $_return[$i]['member_notes'] = $r_mbr['member_notes'];
        $_return[$i]['is_pending'] = $r_mbr['is_pending'];
        $_return[$i]['mpasswd'] = false;
        $_return[$i]['last_login'] = $r_mbr['last_login'];
        $_return[$i]['last_login_ip'] = $r_mbr['last_login_ip'];
        $_return[$i]['hash']['member'] = sha1(urlencode(serialize($_return[$i])));
        $_return[$i]['input_date'] = $r_mbr['input_date'];
        $_return[$i]['last_update'] = $r_mbr['last_update'];
        $i++;
      }
    }
    return $_return;
  }

  /**
   * Convert array to object
   *
   * @param array $array
   * @return object
   */
  public static function to_object($array)
  {
    return json_decode(json_encode($array));
  }

  /**
   * Send to indexing engine (Solr / ElasticSearch) via REST
   *
   * @param array $array
   * @return void
   */
  public static function update_to_index($array_data)
  {
    global $sysconf;
    if ($sysconf['index']['engine']['type'] == 'solr') {
      #$_url = 'http://172.17.0.4:8983/solr/slims/update';
      $_url = $sysconf['index']['engine']['solr_opts']['host'].':'.$sysconf['index']['engine']['solr_opts']['port'].'/solr/'.$sysconf['index']['engine']['solr_opts']['collection'].'/update';
      $_onlydata = json_encode($array_data);
      $json_data = '{"add":{"doc":'.$_onlydata.',"commitWithin": 5000,"overwrite": true}}';
      $ch = curl_init($_url);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);                                                                  
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
      curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
        'Content-Type: application/json',                                                                                
        'Content-Length: ' . strlen($json_data))                                                                    
        );
        curl_exec($ch);
    } elseif ($sysconf['index']['engine']['type'] == 'es') {
      #here is the codes for accessing ES
    }
  }

}

require_once 'member_api.inc.php';
require_once 'circulation_api.inc.php';
