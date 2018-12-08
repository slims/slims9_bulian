<?php
/**
 * Class for OAI-PMH Web Services
 *
 * Copyright (C) 2012  Arie Nugraha (dicarve@yahoo.com)
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

class OAI_Web_Service {
  private $db = false;
  private $xml = false;

  public function __construct($obj_db = false) {
    $this->db = $obj_db;
    $this->xml = new XMLWriter();
    $this->xml->openMemory();
    $this->xml->setIndent(true);
  }

  /**
   * Menampilkan seluruh ID (Identifiers) record/dokumen yang ada repositori
   */
  public function ListIdentifiers($metadataPrefix = 'oai_dc') {
    global $sysconf;

    $resumptionToken = array();
    $offset = 0;
    $where = '';
    $metadataPrefix = 'oai_dc';

    if (isset($_GET['resumptionToken'])) {
      list($metadataPrefix, $offset) = explode('/', $_GET['resumptionToken'], 2);
      if (isset($offset)) {
        $offset = (integer)$offset;
      }
     } else {
      if (isset($_GET['offset'])) {
        $offset = (integer)$_GET['offset'];
      }
      if (isset($_GET['metadataPrefix'])) {
        $metadataPrefix = $_GET['metadataPrefix'];
      }
    }

    // total query
    $num_rec_q = $this->db->query("SELECT COUNT(*) FROM biblio $where");
    $num_data = $num_rec_q->fetch_row();
    $completeListSize = $num_data[0];

    $rec_q = $this->db->query("SELECT biblio_id, last_update FROM biblio $where
      ORDER BY biblio_id DESC LIMIT ".$sysconf['OAI']['ListRecords']['RecordPerSet']." OFFSET $offset");

    $this->xml->startElement('request');
        $this->xml->writeAttribute('verb', 'ListIdentifiers');
        $this->xml->writeAttribute('metadataPrefix', $metadataPrefix);
        $this->xmlWrite($this->xml, $sysconf['OAI']['Identify']['baseURL']);
    $this->xml->endElement();
    $this->xml->startElement('ListIdentifiers');
    // mulai iterasi record
    while ($rec_data = $rec_q->fetch_assoc()) {
      $this->xml->startElement('header');
        $this->xml->writeElement('identifier', $sysconf['OAI']['identifierPrefix'].$rec_data['biblio_id']);
        $this->xml->writeElement('datestamp', $rec_data['last_update']);
      $this->xml->endElement();
    }

    // resumptionToken
    if ($completeListSize > $sysconf['OAI']['ListRecords']['RecordPerSet']) {
      $next_offset = $offset+$sysconf['OAI']['ListRecords']['RecordPerSet'];
      if ($next_offset < $completeListSize) {
        $this->xml->startElement('resumptionToken');
        $this->xml->writeAttribute('completeListSize', $completeListSize);
        $this->xml->writeAttribute('cursor', $offset);
        $this->xml->text($metadataPrefix.'/'.$next_offset);
        $this->xml->endElement();
      }
    }

    $this->xml->endElement();

    $ListIdentifiers = $this->xml->flush();

    return $ListIdentifiers;
  }


  /**
   * Menampilkan seluruh Set/Kategori yang ada pada repositori
   */
  public function ListSets() {
    global $sysconf;

    $resumptionToken = array();
    $offset = 0;
    $where = '';
    $metadataPrefix = 'oai_dc';

    if (isset($_GET['resumptionToken'])) {
      $offset = (integer)$_GET['resumptionToken'];
    } else {
      if (isset($_GET['offset'])) {
        $offset = (integer)$_GET['offset'];
      }
    }

    // total query
    $num_rec_q = $this->db->query("SELECT COUNT(*) FROM mst_topic $where");
    $num_data = $num_rec_q->fetch_row();
    $completeListSize = $num_data[0];

    $set_q = $this->db->query("SELECT * FROM mst_topic $where ORDER BY topic ASC LIMIT ".$sysconf['OAI']['ListRecords']['RecordPerSet']." OFFSET $offset");

    ob_start();
    $this->xml->startElement('request');
        $this->xml->writeAttribute('verb', 'ListSets');
        $this->xmlWrite($this->xml, $sysconf['OAI']['Identify']['baseURL']);
    $this->xml->endElement();

    $this->xml->startElement('ListSets');
    // mulai iterasi record
    while ($set_data = $set_q->fetch_assoc()) {
        $this->xml->startElement('set');
        $this->xml->writeElement('setSpec', $set_data['topic_id']);
        $this->xml->writeElement('setName', "Subject = ".$set_data['topic']);
        $this->xml->endElement();
    }

    // resumptionToken
    if ($completeListSize > $sysconf['OAI']['ListRecords']['RecordPerSet']) {
      $next_offset = $offset+$sysconf['OAI']['ListRecords']['RecordPerSet'];
      if ($next_offset < $completeListSize) {
        $this->xml->startElement('resumptionToken');
        $this->xml->writeAttribute('completeListSize', $completeListSize);
        $this->xml->writeAttribute('cursor', $offset);
        $this->xml->text($next_offset);
        $this->xml->endElement();
      }
    }

    $this->xml->endElement();

    $ListSets = $this->xml->flush();

    return $ListSets;
  }


  /**
   * Menampilkan seluruh Deskripsi Record pada repositori
   */
  public function ListRecords($metadataPrefix = 'oai_dc') {
    global $sysconf;

    $resumptionToken = array();
    $offset = 0;
    $from = null;
    $until = null;
    $where = '';
    $metadataPrefix = 'oai_dc';

    if (isset($_GET['resumptionToken'])) {
      list($metadataPrefix, $offset) = explode('/', $_GET['resumptionToken'], 2);
      if (isset($offset)) {
        $offset = (integer)$offset;
      }
     } else {
      if (isset($_GET['offset'])) {
        $offset = (integer)$_GET['offset'];
      }
      if (isset($_GET['metadataPrefix'])) {
        $metadataPrefix = $_GET['metadataPrefix'];
      }
    }

    if (isset($_GET['from'])) {
        $from = $this->db->escape_string($_GET['from']);
        $date = date_create($from);
        $from_date = $date->format('Y-m-d H:i:s');
    }
    if (isset($_GET['from']) && isset($_GET['until'])) {
        $until = $this->db->escape_string($_GET['until']);
        $date = date_create($until);
        $until_date = $date->format('Y-m-d H:i:s');
    }
    if ($from && $until) {
        $where = "WHERE input_date >= '$from_date' AND input_date <= '$until_date'";
    }

    // total query
    $num_rec_q = $this->db->query("SELECT COUNT(*) FROM biblio $where");
    $num_data = $num_rec_q->fetch_row();
    $completeListSize = $num_data[0];

    $rec_q = $this->db->query("SELECT biblio_id FROM biblio $where ORDER BY biblio_id DESC LIMIT ".$sysconf['OAI']['ListRecords']['RecordPerSet']." OFFSET $offset");

    ob_start();
    $this->xml->startElement('request');
        $this->xml->writeAttribute('verb', 'ListRecords');
        $this->xml->writeAttribute('metadataPrefix', $metadataPrefix);
        $this->xmlWrite($this->xml, $sysconf['OAI']['Identify']['baseURL']);
    $this->xml->endElement();
    echo $this->xml->flush();
    echo "<ListRecords>\n";
    // mulai iterasi record
    while ($rec_data = $rec_q->fetch_row()) {
      echo $this->outputRecordXML($rec_data[0], $metadataPrefix);
    }

    // resumptionToken
    if ($completeListSize > $sysconf['OAI']['ListRecords']['RecordPerSet']) {
      $next_offset = $offset+$sysconf['OAI']['ListRecords']['RecordPerSet'];
      if ($next_offset < $completeListSize) {
        $this->xml->startElement('resumptionToken');
        $this->xml->writeAttribute('completeListSize', $completeListSize);
        $this->xml->writeAttribute('cursor', $offset);
        $this->xml->text($metadataPrefix.'/'.$next_offset);
        $this->xml->endElement();
        echo $this->xml->flush();
      }
    }

    echo "</ListRecords>\n";

    $ListRecords = ob_get_clean();

    return $ListRecords;
  }


  /**
   * Menampilkan data sebuah record pada repositori
   */
  public function GetRecord($recordID, $metadataPrefix = 'oai_dc') {
    global $sysconf;
    ob_start();
    // check record ID
    /*
    if (strpos($recordID, $sysconf['OAI']['identifierPrefix']) !== true) {
      echo '<request verb="GetRecord" identifier="'.$recordID.'" metadataPrefix="'.$metadataPrefix.'">'.$sysconf['OAI']['Identify']['baseURL'].'</request>'."\n";
      echo '<error code="idDoesNotExist">No matching identifier in our Repository</error>';
      return ob_get_clean();
    }
    */
    $recordID = str_ireplace($sysconf['OAI']['identifierPrefix'], '', $recordID);
    $this->xml->startElement('request');
        $this->xml->writeAttribute('verb', 'GetRecord');
        $this->xml->writeAttribute('identifier', $recordID);
        $this->xml->writeAttribute('metadataPrefix', $metadataPrefix);
        $this->xmlWrite($this->xml, $sysconf['OAI']['Identify']['baseURL']);
    $this->xml->endElement();
    echo $this->xml->flush();
    // echo '<request verb="GetRecord" identifier="'.$recordID.'" metadataPrefix="'.$metadataPrefix.'">'.$sysconf['OAI']['Identify']['baseURL'].'</request>'."\n";
    echo "<GetRecord>\n";
    echo $this->outputRecordXML($recordID, $metadataPrefix);
    echo "</GetRecord>\n";
    $GetRecord = ob_get_clean();

    return $GetRecord;
  }


  /**
   * Menampilkan data mengenai repository ini
   */
  public function Identify() {
    global $sysconf;
    $earliestDatestamp = '';
    $this->xml->startElement('request'); $this->xml->writeAttribute('verb', 'Identify'); $this->xml->text($sysconf['OAI']['Identify']['baseURL']); $this->xml->endElement();
    $this->xml->startElement('Identify');
        $this->xml->writeElement('repositoryName', $sysconf['OAI']['Identify']['repositoryName']);
        $this->xml->writeElement('baseURL', $sysconf['OAI']['Identify']['baseURL']);
        $this->xml->writeElement('protocolVersion', '2.0');
        $this->xml->writeElement('repositoryName', $sysconf['OAI']['Identify']['repositoryName']);
        $this->xml->writeElement('adminEmail', $sysconf['OAI']['Identify']['adminEmail']);
        $this->xml->writeElement('earliestDatestamp', $earliestDatestamp);
        $this->xml->writeElement('deletedRecord', $sysconf['OAI']['Identify']['deletedRecord']);
        $this->xml->writeElement('granularity', $sysconf['OAI']['Identify']['granularity']);
        $this->xml->startElement('description');
             $this->xml->startElement('eprints');
             $this->xml->writeAttribute('xmlns', 'http://www.openarchives.org/OAI/1.1/eprints');
             $this->xml->writeAttribute('xsi:schemaLocation', 'http://www.openarchives.org/OAI/1.1/eprints http://www.openarchives.org/OAI/1.1/eprints.xsd');
                $this->xml->startElement('metadataPolicy');
                    $this->xml->writeElement('text', $sysconf['OAI']['Identify']['metadataPolicy']);
                $this->xml->endElement();
             $this->xml->endElement();
        $this->xml->endElement();
    $this->xml->endElement();
    return $this->xml->flush();
  }


  /**
   * Menampilkan seluruh skema metadata yang didukung oleh repositori ini
   */
  public function ListMetadataFormats() {
    global $sysconf;
    $this->xml->startElement('request');
        $this->xml->writeAttribute('verb', 'ListMetadataFormats');
        $this->xml->text($sysconf['OAI']['Identify']['baseURL']);
    $this->xml->endElement();
    $this->xml->startElement('ListMetadataFormats');
    // query ke database
    foreach ($sysconf['OAI']['MetadataFormats'] as $metadataformat) {
      $this->xml->startElement('metadataFormat');
        $this->xml->writeElement('metadataPrefix', $metadataformat['oai_prefix']);
        $this->xml->writeElement('schema', $metadataformat['schema_xsd']);
        $this->xml->writeElement('metadataNamespace', $metadataformat['namespace']);
      $this->xml->endElement();
    }
    $this->xml->endElement();
    $ListMetadataFormats = $this->xml->flush();

    return $ListMetadataFormats;
  }


  /**
   * Menampilkan detil record metadata dalam bentuk XML
   *
   * @param   mixed   $recordID: ID atau Identifier OAI dari record
   * @param   string  $metadataPrefix:  Prefix dari skema metadata yang diinginkan
   * @param   boolean $headerOnly: tampilkan hanya header dari record XML OAI
   *
   */
  protected function outputRecordXML($recordID, $metadataPrefix = 'oai_dc') {
    global $sysconf;

    // ambil detail record
    if ($metadataPrefix == 'oai_dc') {
      $detail = new detail($this->db, $recordID, 'dc');
      $rec_detail = $detail->DublinCoreOutput();
    }

    // mulai output XML
    ob_start();
    echo "<record><header><identifier>".$sysconf['OAI']['identifierPrefix'].$recordID."</identifier></header>";
    echo "<metadata>";
    if ($metadataPrefix == 'oai_dc') {
      echo '<oai_dc:dc xmlns:oai_dc="http://www.openarchives.org/OAI/2.0/oai_dc/" xmlns:dc="http://purl.org/dc/elements/1.1/" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
    }

    echo $rec_detail;

    if ($metadataPrefix == 'oai_dc') {
      echo '</oai_dc:dc>';
    }

    echo "</metadata>\n";
    echo "</record>\n";
    $recordXML = ob_get_clean();

    return $recordXML;
  }

  private function xmlWrite(&$xmlwriter, $data, $mode = 'Text') {
    if ($mode == 'CData') {
        $xmlwriter->writeCData($data);
    } else {
        $xmlwriter->text($data);
    }
  }
}
