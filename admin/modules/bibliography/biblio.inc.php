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

class Biblio
{
  private $db = false;
  private $biblio_id = 0;
  private $is_new = false;
  private $error = false;
  private $record_detail = false;
  private $records = false;
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

  public function getRecords($criteria = '', $offset = 0, $total = 10000)
  {
    $_sql = 'SELECT b.*, l.language_name AS `language`, p.publisher_name AS `publisher`,
        pl.place_name AS `publish_place`, gmd.gmd_name AS `gmd`, fr.frequency,
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
      LEFT JOIN mst_carrier_type AS rcrt ON b.carrier_type_id=rcrt.id';
    if ($criteria) {
      $_sql .= ' WHERE ' . $criteria;
      $_sql .= " LIMIT $offset, $total";
    } else {
      $_sql .= " LIMIT $offset, $total";
    }

    $_q = $this->db->query($_sql);
    if ($this->db->error) {
      $this->error = $this->db->error;
      echo $_sql;
      die();
      return false;
    } else {
      $this->records = [];
      while ($record = $_q->fetch_assoc()) {
          $this->records[] = $record;
      }
      // free the memory
      $_q->free_result();
      return $this->records;
    }
  }

  /**
   * Method to get file attachments information of biblio
   *
   *
   * @return  array
   */
  public function getAttachments()
  {
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
  public function getItemCopy()
  {
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
  public function getAuthors()
  {
    $items = array();
    // get the authors data
    $_biblio_authors_q = $this->db->query(sprintf('SELECT author_name, authority_type, auth_list, level FROM mst_author AS a'
      . ' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id=%d ORDER BY level ASC', $this->biblio_id));
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
  public function getSubjects()
  {
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


  public function detail($format = 'full')
  {
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


  /**
   *
   * Function to export biblio data to MARC records
   *
   **/
  public function marc_export($input_id = 0, $offset = 0, $total = 10000, $format = 'RAW')
  {
    global $dbs;
    $marc_records = '';
    $records = array();
    if ($total > 1000000) {
      $total = 10000;
    }
    if ($total < 1) {
      $total = 1000000;
    }
    $input_id = $dbs->escape_string($input_id);
    if ($input_id == 'BATCH') {
      $records = $this->getRecords(null, $offset, $total);
    } else {
      $records = $this->getRecords(sprintf('biblio_id IN (%s)', $input_id), $offset, $total);
    }

    foreach ($records as $_recs) {
      $marc = new File_MARC_Record();

      if (isset($_recs['title']) && $_recs['title'] <> "") {
        $main_title = preg_replace('@:.+$@i', '', $_recs['title']);
        $rest_title = preg_replace('@^.+:@i', '', $_recs['title']);

        if ($main_title <> $rest_title) {
          $tag['245'][] = new File_MARC_Subfield('a', preg_replace('/:.+$/i', '', $_recs['title']));
          $tag['245'][] = new File_MARC_Subfield('b', ' : ' . preg_replace('/^.+:/i', '', $_recs['title']));
        } else {
          $tag['245'][] = new File_MARC_Subfield('a', $_recs['title']);
        }

        if (isset($_recs['sor']) && $_recs['sor'] <> "") {
          $tag['245'][] = new File_MARC_Subfield('c', $_recs['sor']);
        }
        if (isset($_recs['gmd']) && $_recs['gmd'] <> "") {
          $tag['245'][] = new File_MARC_Subfield('h', $_recs['gmd']);
        }
        $marc->appendField(new File_MARC_Data_Field('245', $tag['245'], 0), null, null);
        // $tag['245'] = $sd.'a'.$_recs['title'].$sd.'h'.$_recs['gmd'];
      }
      if (isset($_recs['isbn_issn']) && $_recs['isbn_issn'] <> "") {
        $marc->appendField(new File_MARC_Data_Field('020', array(
          new File_MARC_Subfield('a', $_recs['isbn_issn']),
        ), null, null
        ));
        // $tag['020'] = $sd.'a'.$_recs['isbn_issn'];
      }
      if (isset($_recs['edition']) && $_recs['edition'] <> "") {
        $marc->appendField(new File_MARC_Data_Field('250', array(
          new File_MARC_Subfield('a', $_recs['edition']),
        ), null, null
        ));
        //$tag['250'] = $sd.'a'.$_recs['edition'];
      }
      // $tag[] = $_recs['author'];
      // get author name and roles first
      $_aut_q = $dbs->query('SELECT a.author_name,a.author_year,a.authority_type,i.level FROM biblio_author as i LEFT JOIN `mst_author` as a on a.author_id=i.author_id WHERE i.biblio_id=' . $_recs['biblio_id']);
      while ($_rs_aut = $_aut_q->fetch_assoc()) {
        if ($_rs_aut['level'] == 1) {
          if ($_rs_aut['authority_type'] == 'p') {
            $marc->appendField(new File_MARC_Data_Field('100', array(
              new File_MARC_Subfield('a', $_rs_aut['author_name']),
            ), null, null
            ));
            //$tag['100'] = $sd.'a'.$_rs_aut['author_name'];
          } elseif ($_rs_aut['authority_type'] == 'o') {
            $marc->appendField(new File_MARC_Data_Field('110', array(
              new File_MARC_Subfield('a', $_rs_aut['author_name']),
            ), null, null
            ));
            //$tag['110'] = $sd.'a'.$_rs_aut['author_name'];
          } elseif ($_rs_aut['authority_type'] == 'c') {
            $marc->appendField(new File_MARC_Data_Field('111', array(
              new File_MARC_Subfield('a', $_rs_aut['author_name']),
            ), null, null
            ));
            //$tag['111'] = $sd.'a'.$_rs_aut['author_name'];
          }
        } else {
          if ($_rs_aut['authority_type'] == 'p') {
            if (!isset($tag['700'])) {
              $marc->appendField(new File_MARC_Data_Field('700', array(
                new File_MARC_Subfield('a', $_rs_aut['author_name']),
              ), null, null
              ));
            } elseif ($_rs_aut['authority_type'] == 'o') {
              $marc->appendField(new File_MARC_Data_Field('710', array(
                new File_MARC_Subfield('a', $_rs_aut['author_name']),
              ), null, null
              ));
            } elseif ($_rs_aut['authority_type'] == 'c') {
              $marc->appendField(new File_MARC_Data_Field('711', array(
                new File_MARC_Subfield('a', $_rs_aut['author_name']),
              ), null, null
              ));
            }
          }
        }
      }
      // $tag[] = $_recs['topic'];
      // get topic and its type first
      $_top_q = $dbs->query('SELECT t.topic,t.topic_type,i.level FROM biblio_topic as i LEFT JOIN `mst_topic` as t on t.topic_id=i.topic_id WHERE i.biblio_id=' . $_recs['biblio_id']);
      while ($_rs_top = $_top_q->fetch_assoc()) {
        if ($_rs_top['topic_type'] == 't') {
          if (!isset($tag['650'])) {
            $marc->appendField(new File_MARC_Data_Field('650', array(
              new File_MARC_Subfield('a', $_rs_top['topic']),
            ), null, null
            ));
          }
        } elseif ($_rs_top['topic_type'] == 'n') {
          if (!isset($tag['600'])) {
            $marc->appendField(new File_MARC_Data_Field('600', array(
              new File_MARC_Subfield('a', $_rs_top['topic']),
            ), null, null
            ));
          }
        } elseif ($_rs_top['topic_type'] == 'c') {
          if (!isset($tag['610'])) {
            $marc->appendField(new File_MARC_Data_Field('610', array(
              new File_MARC_Subfield('a', $_rs_top['topic']),
            ), null, null
            ));
          }
        } elseif ($_rs_top['topic_type'] == 'g') {
          if (!isset($tag['651'])) {
            $marc->appendField(new File_MARC_Data_Field('651', array(
              new File_MARC_Subfield('a', $_rs_top['topic']),
            ), null, null
            ));
          }
        } elseif ($_rs_top['topic_type'] == 'tm' || $_rs_top['topic_type'] == 'oc') {
          if (!isset($tag['653'])) {
            $marc->appendField(new File_MARC_Data_Field('653', array(
              new File_MARC_Subfield('a', $_rs_top['topic']),
            ), null, null
            ));
          }
        } elseif ($_rs_top['topic_type'] == 'gr') {
          if (!isset($tag['655'])) {
            $marc->appendField(new File_MARC_Data_Field('655', array(
              new File_MARC_Subfield('a', $_rs_top['topic']),
            ), null, null
            ));
          }
        }
      }
      $marc->appendField(new File_MARC_Data_Field('005', array(
        new File_MARC_Subfield('a', preg_replace("(-|:| )", "", $_recs['last_update'])),
      ), null, null
      ));
      //$tag['005'] = $sd.'a'.preg_replace("(-|:| )", "", $_recs['last_update']);
      $marc->appendField(new File_MARC_Data_Field('260', array(
        new File_MARC_Subfield('a', $_recs['publish_place']),
        new File_MARC_Subfield('b', $_recs['publisher']),
        new File_MARC_Subfield('c', $_recs['publish_year']),
      ), null, null
      ));
      //$tag['260'] = $sd.'a'.$_recs['publish_place'].$sd.'b'.$_recs['publisher'].$sd.'c'.$_recs['publish_year'];
      $marc->appendField(new File_MARC_Data_Field('041', array(
        new File_MARC_Subfield('a', $_recs['language']),
      ), null, null
      ));
      //$tag['041'] = $sd.'a'.$_recs['language'];
      $marc->appendField(new File_MARC_Data_Field('084', array(
        new File_MARC_Subfield('a', $_recs['classification']),
      ), null, null
      ));
      //$tag['084'] = $sd.'a'.$_recs['classification'];
      //$tag['245'] = $_recs['spec_detail_info'];
      if (isset($_recs['collation']) && $_recs['collation'] <> "") {

        $collation = preg_split('/:|;/', $_recs['collation']);
        $extent = trim($collation[0]) . ' :';
        if (preg_match('/:/', $_recs['collation'])) {
          $other_physical_details = trim($collation[1]) . ' ;';
        }
        if (preg_match('/;/', $_recs['collation'])) {
          $collation = preg_split('/;/', $_recs['collation']);
          $dimensions = trim($collation[1]);
        }

        $marc->appendField(new File_MARC_Data_Field('300', array(
          new File_MARC_Subfield('a', $extent),
          new File_MARC_Subfield('b', $other_physical_details ?? ''),
          new File_MARC_Subfield('c', $dimensions ?? '')
        ), null, null
        ));
        //$tag['300'] = $sd.'a'.preg_replace("/;/", ";".$sd."c", preg_replace("/:/", ":".$sd."b", $_recs['collation']));
      }
      if (isset($_recs['notes']) && $_recs['notes'] <> "") {
        $marc->appendField(new File_MARC_Data_Field('500', array(
          new File_MARC_Subfield('a', str_ireplace(array("\n", "\r"), '', $_recs['notes'])),
        ), null, null
        ));
        //$tag['500'] = $sd.'a'.$_recs['notes'];
      }
      if (isset($_recs['series_title']) && $_recs['series_title'] <> "") {
        $marc->appendField(new File_MARC_Data_Field('490', array(
          new File_MARC_Subfield('a', $_recs['series_title']),
        ), null, null
        ));
        //$tag['490'] = $sd.'a'.$_recs['series_title'];
      }
      if (isset($_recs['content_type']) && $_recs['content_type'] <> "") {
        $marc->appendField(new File_MARC_Data_Field('336', array(
          new File_MARC_Subfield('a', $_recs['content_type']),
        ), null, null
        ));
        //$tag['336'] = $sd.'a'.$_recs['content_type'];
      }
      if (isset($_recs['media_type']) && $_recs['media_type'] <> "") {
        $marc->appendField(new File_MARC_Data_Field('337', array(
          new File_MARC_Subfield('a', $_recs['media_type']),
        ), null, null
        ));
        //$tag['337'] = $sd.'a'.$_recs['media_type'];
      }
      if (isset($_recs['carrier_type']) && $_recs['carrier_type'] <> "") {
        $marc->appendField(new File_MARC_Data_Field('338', array(
          new File_MARC_Subfield('a', $_recs['carrier_type']),
        ), null, null
        ));
        //$tag['338'] = $sd.'a'.$_recs['carrier_type'];
      }

      //print_r($tag);
      /*
      $fh = fopen($filename, 'w');
      fwrite($fh, $marc->toRaw());
      fclose($fh);
      */
      unset($tag);
      if ($format == 'XML') {
        $marc_records .= preg_replace('@<\?xml.+?>@i', '', $marc->toXML('UTF-8', true, false));
      } else if ($format == 'JSON') {
        $marc_records .= $marc->toJSON() . ',';
      } else {
        $marc_records .= $marc->toRaw();
      }
    }

    if ($format == 'XML') {
      $output = '<?xml version="1.0" encoding="UTF-8"?>' .
        '<marc:collection xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' .
        'xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">';
      $output .= $marc_records;
      $output .= '</marc:collection>';
      return $output;
    } else if ($format == 'JSON') {
      $output = '[';
      $output .= substr_replace($marc_records, '', -1);
      $output .= ']';
      return $output;
    }

    return $marc_records;
  }

  /**
   * @return mixed
   */
  public function getError()
  {
    return $this->error;
  }


}