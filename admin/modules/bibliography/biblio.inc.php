<?php
/**
 * Copyright (C) 2015 Arie Nugraha (dicarve@yahoo.com)
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

class Biblio {
  private $db = false;
  private $biblio_id = 0;
  private $is_new = false;
  private $error = false;
  private $record_detail = false;
  public $title = '';
  
  public function __construct($dbs, $biblio_id = 0)
  {
    $this->db = $dbs;
    $this->biblio_id = $biblio_id;
    if (!$biblio_id) {
      $this->is_new = true;
    } else {
        $_sql = sprintf('SELECT b.*, l.language_name, p.publisher_name,
            pl.place_name AS `publish_place`, gmd.gmd_name, fr.frequency,
            rct.content_type,
            rmt.media_type, rcrt.carrier_type
            FROM biblio AS b
          LEFT JOIN mst_gmd AS gmd ON b.gmd_id=gmd.gmd_id
          LEFT JOIN mst_language AS l ON b.language_id=l.language_id
          LEFT JOIN mst_publisher AS p ON b.publisher_id=p.publisher_id
          LEFT JOIN mst_place AS pl ON b.publish_place_id=pl.place_id
          LEFT JOIN mst_frequency AS fr ON b.frequency_id=fr.frequency_id
          LEFT JOIN mst_content_type rct ON b.content_type_id=rct.id
          LEFT JOIN mst_media_type AS rmt ON b.media_type_id=rmt.id
          LEFT JOIN mst_carrier_type AS rcrt ON b.carrier_type_id=rcrt.id
          WHERE biblio_id=%d', $this->biblio_id);
        $_det_q = $this->db->query($_sql);
        if ($this->db->error) {
          $this->error = $this->db->error;
        } else {
          $this->record_detail = $_det_q->fetch_assoc();
          // free the memory
          $_det_q->free_result();
        }
    }
  }

  /**
   * Method to get file attachments information of biblio
   *
   *
   * @return  array
   */
  public function getAttachments() {
    $items = array();
    $_output = '';
    $attachment_q = $this->db->query(sprintf('SELECT att.*, f.* FROM biblio_attachment AS att
      LEFT JOIN files AS f ON att.file_id=f.file_id WHERE att.biblio_id=%d AND att.access_type=\'public\' LIMIT 20', $this->biblio_id));
    while ($attachment_d = $attachment_q->fetch_assoc()) {
      $items[] = $attachment_d;
    }
    $attachment_q->free_result();
    return $items;
  }


  /**
   * Method to get items/copies information of biblio
   *
   *
   * @return  array
   */
  public function getItemCopy() {
    $items = array();
    $_output = '';
    $copy_q = $this->db->query(sprintf('SELECT i.item_code, i.call_number, loc.location_name, stat.*, i.site FROM item AS i
        LEFT JOIN mst_item_status AS stat ON i.item_status_id=stat.item_status_id
        LEFT JOIN mst_location AS loc ON i.location_id=loc.location_id
        WHERE i.biblio_id=%d', $this->biblio_id));
    while ($copy_d = $copy_q->fetch_assoc()) {
      $items[] = $copy_d;
    }
    $copy_q->free_result();
    return $items;
  }
  
  /**
   * Method to get authors information of biblio
   *
   *
   * @return  array
   */
  public function getAuthors() {
    $items = array();
    // get the authors data
    $_biblio_authors_q = $this->db->query(sprintf('SELECT author_name, authority_type, auth_list, level FROM mst_author AS a'
        .' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id=%d ORDER BY level ASC', $this->biblio_id));
    while ($data = $_biblio_authors_q->fetch_assoc()) {
        if ($data['authority_type'] == 'p') {
            $data['authority_type'] = "Personal Name";
        } elseif ($data['authority_type'] == 'o') {
            $data['authority_type'] = "Organizational Body";
        } elseif ($data['authority_type'] == 'c') {
            $data['authority_type'] = "Conference";
        }
        $items[] = $data;
    }
    $_biblio_authors_q->free_result();
    return $items;
  }

  /**
   * Method to get subjects information of biblio
   *
   *
   * @return  array
   */  
  public function getSubjects() {
    $items = array();
    // get the topics data
    $_biblio_topics_q = $this->db->query(sprintf('SELECT topic, topic_type, auth_list FROM mst_topic AS a
      LEFT JOIN biblio_topic AS ba ON a.topic_id=ba.topic_id WHERE ba.biblio_id=%d', $this->biblio_id));
    while ($data = $_biblio_topics_q->fetch_assoc()) {
      $items[] = $data;
    }
    // free memory
    $_biblio_topics_q->free_result();
    return $items;
  }

  
  public function detail($format = 'full') {
    if (!$this->biblio_id) {
      return false;
    }
    if ($format == 'full') {
      $this->record_detail['authors'] = $this->getAuthors();
      $this->record_detail['subjects'] = $this->getSubjects();
      $this->record_detail['copies'] = $this->getItemCopy();
      $this->record_detail['attachments'] = $this->getAttachments();        
    }

    return $this->record_detail;    
  }
}