<?php
/**
 * OAI-PMH
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

// key to authenticate
define('INDEX_AUTH', '1');

// required file
require 'sysconfig.inc.php';
$date_respons = date('Y-m-d').'T'.date('H:i:s').'Z';

if (!$sysconf['OAI']['enable']) {
  header('Content-type: text/xml');
  echo '<?xml version="1.0" encoding="UTF-8"?><OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
  http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
  <responseDate>'.$date_respons.'</responseDate>
  <request>'.$sysconf['OAI']['Identify']['baseURL'].'</request>
  <error code="badVerb">OAI Repository disabled</error>
  </OAI-PMH>';
  exit();
}

// required library
require LIB.'oai-pmh.inc.php';
require LIB.'detail.inc.php';

$config['oai_pmh_verbs'] = array(
  'GetRecord',
  'Identify',
  'ListMetadataFormats',
  'ListIdentifiers',
  'ListRecords',
  'ListSets'
  );

// cek apakah ada request OAI-PMH pada REQUEST HTTP GET atau POST
if (isset($_GET['verb']) || isset($_POST['verb'])) {
  $oai_verb = isset($_GET['verb'])?trim($_GET['verb']):trim($_POST['verb']);
  if (in_array($oai_verb, $config['oai_pmh_verbs'])) {
    // MULAI PROSES OAI-PMH REQUEST
    // buat instance object OAI-PMH
    $oai_respon_handlers = new OAI_Web_Service($dbs);

    // mulai output XML
    header('Content-type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
      .'<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">'."\n"
      .'<responseDate>'.$date_respons.'</responseDate>'."\n";

    switch ($oai_verb) {
      case 'ListSets';
        echo $oai_respon_handlers->ListSets();
        break;
      case 'ListIdentifiers';
        $metadataPrefix = isset($_GET['metadataPrefix'])?$dbs->escape_string(trim($_GET['metadataPrefix'])):'oai_dc';
        echo $oai_respon_handlers->ListIdentifiers($metadataPrefix);
        break;
      case 'ListMetadataFormats';
        echo $oai_respon_handlers->ListMetadataFormats();
        break;
      case 'ListRecords';
        $metadataPrefix = isset($_GET['metadataPrefix'])?$dbs->escape_string(trim($_GET['metadataPrefix'])):'oai_dc';
        echo $oai_respon_handlers->ListRecords($metadataPrefix);
        break;
      case 'GetRecord';
        $identifier = isset($_GET['identifier'])?$dbs->escape_string(trim($_GET['identifier'])):'0';
        $metadataPrefix = $dbs->escape_string(trim($_GET['metadataPrefix']));
        echo $oai_respon_handlers->GetRecord($identifier, $metadataPrefix);
        break;
      default:
        echo $oai_respon_handlers->Identify();
        break;
    }

    echo '</OAI-PMH>';
  } else {
    // mulai output XML
    header('Content-type: text/xml');
    echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
      .'<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">'."\n"
      .'<responseDate>'.$date_respons.'</responseDate>'."\n";

    echo '<request>'.$sysconf['OAI']['Identify']['baseURL'].'</request>
      <error code="badVerb">Illegal OAI verb</error>
      </OAI-PMH>';
  }
  exit();
} else {
  // mulai output XML
  header('Content-type: text/xml');
  echo '<?xml version="1.0" encoding="UTF-8"?>'."\n"
    .'<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">'."\n"
    .'<responseDate>'.$date_respons.'</responseDate>'."\n";

  echo '<request>'.$sysconf['OAI']['Identify']['baseURL'].'</request>
    <error code="badVerb">Illegal OAI verb</error>
    </OAI-PMH>';
  exit();
}
