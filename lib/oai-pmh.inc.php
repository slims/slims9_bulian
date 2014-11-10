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

  public function __construct($obj_db = false) {
    $this->db = $obj_db;
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
      echo 'resumption';
      parse_str($_GET['resumptionToken'], $resumptionToken);
      if (isset($resumptionToken['offset'])) {
        $offset = (integer)$resumptionToken['offset'];
      }
      if (isset($resumptionToken['metadataPrefix'])) {
        $metadataPrefix = $resumptionToken['metadataPrefix'];
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

    ob_start();
    echo '<request verb="ListIdentifiers" metadataPrefix="'.$metadataPrefix.'">'.$sysconf['OAI']['Identify']['baseURL'].'</request>'."\n";
    echo "<ListIdentifiers>\n";
    // mulai iterasi record
    while ($rec_data = $rec_q->fetch_assoc()) {
      echo "<header>\n<identifier>".$sysconf['OAI']['identifierPrefix'].$rec_data['biblio_id']."</identifier><datestamp>".$rec_data['last_update']."</datestamp></header>\n";
    }

    // resumptionToken
    if ($completeListSize > $sysconf['OAI']['ListRecords']['RecordPerSet']) {
      $next_offset = $offset+$sysconf['OAI']['ListRecords']['RecordPerSet'];
      if ($next_offset < $completeListSize) {
        echo '<resumptionToken completeListSize="'.$completeListSize.'">'.urlencode("offset=$next_offset").'</resumptionToken>'."\n";
      }
    }

    echo "</ListIdentifiers>\n";

    $ListIdentifiers = ob_get_clean();

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
      echo 'resumption';
      parse_str($_GET['resumptionToken'], $resumptionToken);
      if (isset($resumptionToken['offset'])) {
        $offset = (integer)$resumptionToken['offset'];
      }
      if (isset($resumptionToken['metadataPrefix'])) {
        $metadataPrefix = $resumptionToken['metadataPrefix'];
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
    $num_rec_q = $this->db->query("SELECT COUNT(*) FROM mst_topic $where");
    $num_data = $num_rec_q->fetch_row();
    $completeListSize = $num_data[0];

    $set_q = $this->db->query("SELECT * FROM mst_topic $where ORDER BY topic ASC LIMIT ".$sysconf['OAI']['ListRecords']['RecordPerSet']." OFFSET $offset");

    ob_start();
    echo '<request verb="ListSets">'.$sysconf['OAI']['Identify']['baseURL'].'</request>'."\n";
    echo "<ListSets>\n";
    // mulai iterasi record
    while ($set_data = $set_q->fetch_assoc()) {
      echo "<set>\n".
      "<setSpec>".$set_data['topic_id']."</setSpec>\n".
      "<setName>Subject = ".$set_data['topic']."</setName>\n".
      "</set>\n";
    }

    // resumptionToken
    if ($completeListSize > $sysconf['OAI']['ListRecords']['RecordPerSet']) {
      $next_offset = $offset+$sysconf['OAI']['ListRecords']['RecordPerSet'];
      if ($next_offset < $completeListSize) {
        echo '<resumptionToken completeListSize="'.$completeListSize.'">'.urlencode("offset=$next_offset").'</resumptionToken>'."\n";
      }
    }

    echo "</ListSets>\n";

    $ListSets = ob_get_clean();

    return $ListSets;
  }


  /**
   * Menampilkan seluruh Deskripsi Record pada repositori
   */
  public function ListRecords($metadataPrefix = 'oai_dc') {
    global $sysconf;

    $resumptionToken = array();
    $offset = 0;
    $where = '';
    $metadataPrefix = 'oai_dc';

    if (isset($_GET['resumptionToken'])) {
      echo 'resumption';
      parse_str($_GET['resumptionToken'], $resumptionToken);
      if (isset($resumptionToken['offset'])) {
        $offset = (integer)$resumptionToken['offset'];
      }
      if (isset($resumptionToken['metadataPrefix'])) {
        $metadataPrefix = $resumptionToken['metadataPrefix'];
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

    $rec_q = $this->db->query("SELECT biblio_id FROM biblio $where ORDER BY biblio_id DESC LIMIT ".$sysconf['OAI']['ListRecords']['RecordPerSet']." OFFSET $offset");

    ob_start();
    echo '<request verb="ListRecords" metadataPrefix="'.$metadataPrefix.'">'.$sysconf['OAI']['Identify']['baseURL'].'</request>'."\n";
    echo "<ListRecords>\n";
    // mulai iterasi record
    while ($rec_data = $rec_q->fetch_row()) {
      echo $this->outputRecordXML($rec_data[0], $metadataPrefix);
    }

    // resumptionToken
    if ($completeListSize > $sysconf['OAI']['ListRecords']['RecordPerSet']) {
      $next_offset = $offset+$sysconf['OAI']['ListRecords']['RecordPerSet'];
      if ($next_offset < $completeListSize) {
        echo '<resumptionToken completeListSize="'.$completeListSize.'">'.urlencode("offset=$next_offset&metadataPrefix=$metadataPrefix").'</resumptionToken>'."\n";
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
    echo '<request verb="GetRecord" identifier="'.$recordID.'" metadataPrefix="'.$metadataPrefix.'">'.$sysconf['OAI']['Identify']['baseURL'].'</request>'."\n";
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
    ob_start();
    echo '<request verb="Identify">'.$sysconf['OAI']['Identify']['baseURL'].'</request>'."\n";
    ?>
    <Identify>
      <repositoryName><?php echo htmlentities($sysconf['OAI']['Identify']['repositoryName']); ?></repositoryName>
      <baseURL><?php echo htmlentities($sysconf['OAI']['Identify']['baseURL']); ?></baseURL>
      <protocolVersion>2.0</protocolVersion>
      <adminEmail><?php echo $sysconf['OAI']['Identify']['adminEmail']; ?></adminEmail>
      <earliestDatestamp><?php echo $earliestDatestamp; ?></earliestDatestamp>
      <deletedRecord><?php echo $sysconf['OAI']['Identify']['deletedRecord']; ?></deletedRecord>
      <granularity><?php echo $sysconf['OAI']['Identify']['granularity']; ?></granularity>
      <description>
        <eprints xmlns="http://www.openarchives.org/OAI/1.1/eprints" xsi:schemaLocation="http://www.openarchives.org/OAI/1.1/eprints http://www.openarchives.org/OAI/1.1/eprints.xsd">
        <metadataPolicy>
          <text><?php echo htmlentities($sysconf['OAI']['Identify']['metadataPolicy']); ?></text>
        </metadataPolicy>
        </eprints>
      </description>
    </Identify>
    <?php
    return ob_get_clean();
  }


  /**
   * Menampilkan seluruh skema metadata yang didukung oleh repositori ini
   */
  public function ListMetadataFormats() {
    global $sysconf;
    ob_start();
    echo '<request verb="ListMetadataFormats">'.$sysconf['OAI']['Identify']['baseURL'].'</request>'."\n";
    echo "<ListMetadataFormats>\n";
    // query ke database
    foreach ($sysconf['OAI']['MetadataFormats'] as $metadataformat) {
      echo "<metadataFormat>\n";
      echo "<metadataPrefix>".$metadataformat['oai_prefix']."</metadataPrefix>\n".
        "<schema>".$metadataformat['schema_xsd']."</schema>\n".
        "<metadataNamespace>".$metadataformat['namespace']."</metadataNamespace>\n";
    }
    echo "</metadataFormat>\n";
    echo "</ListMetadataFormats>\n";
    $ListMetadataFormats = ob_get_clean();

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

    // berikut adalah entitas yang dilarang oleh XML, tambahkan dalam array apabila diperlukan
    $xmlForbiddenSymbols = array('&Acirc;', '&Atilde;', '&para;',
      '&cedil;', '&copy;', '&shy;', '&pound;', '&plusmn;',
      '&reg;', '&sect;', '&middot;', '&iexcl;');

    // ambil detail record
    if ($metadataPrefix == 'oai_dc') {
      $detail = new detail($this->db, $recordID, 'dc');
      $rec_detail = $detail->DublinCoreOutput();
    }

    // mulai output XML
    ob_start();
    echo '<record>'
     ."<header><identifier>".$sysconf['OAI']['identifierPrefix'].$recordID."</identifier></header>";
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
}
