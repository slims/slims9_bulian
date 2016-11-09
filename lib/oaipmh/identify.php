<?php
/**
 * \file
 * \brief Response to Verb Identify
 *
 * Tell the world what the data provider is. Usually it is static once the provider has been set up.
 *
 * \see http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm for details
 */

// The response to Identify is fixed
if (SHOW_QUERY_ERROR) {
	echo "Here are some settings in raw format:\n";
  print_r($identifyResponse);
	echo 'MAXRECORDS ',MAXRECORDS, ', MAXIDS ', MAXIDS,"\n";
  echo 'Token is valid for ',TOKEN_VALID," seconds\n";
  echo 'Tokens have prefix: ',TOKEN_PREFIX,"\n";
  echo 'XMLSCHEMA: ',XMLSCHEMA,"\n";
  echo "\n";
}
$outputObj = new ANDS_Response_XML($args);
foreach($identifyResponse as $key => $val) {
	$outputObj->add2_verbNode($key, $val);
}

foreach($adminEmail as $val) {
	$outputObj->add2_verbNode("adminEmail", $val);
}

if(isset($compression)) {
	foreach($compression as $val) {
		$outputObj->add2_verbNode("compression", $val);
	}
}

// A description MAY be included.
// Use this if you choose to comply with a specific format of unique identifiers
// for items. 
// See http://www.openarchives.org/OAI/2.0/guidelines-oai-identifier.htm 
// for details

// As they will not be changed, using string for simplicity.
$output = '';
if ($show_identifier && $repositoryIdentifier && $delimiter && $sampleIdentifier) {
	$output .= 
'  <description>
   <oai-identifier xmlns="http://www.openarchives.org/OAI/2.0/oai-identifier"
                   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                   xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai-identifier
                   http://www.openarchives.org/OAI/2.0/oai-identifier.xsd">
    <scheme>oai</scheme>
    <repositoryIdentifier>'.$repositoryIdentifier.'</repositoryIdentifier>
    <delimiter>'.$delimiter.'</delimiter>
    <sampleIdentifier>'.$sampleIdentifier.'</sampleIdentifier>
   </oai-identifier>
  </description>'."\n"; 
}

// A description MAY be included.
// This example from arXiv.org is used by the e-prints community, please adjust
// see http://www.openarchives.org/OAI/2.0/guidelines-eprints.htm for details

// To include, change 'false' to 'true'.
if (false) {
	$output .= 
'  <description>
   <eprints xmlns="http://www.openarchives.org/OAI/1.1/eprints"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.openarchives.org/OAI/1.1/eprints 
            http://www.openarchives.org/OAI/1.1/eprints.xsd">
    <content>
     <text>Author self-archived e-prints</text>
    </content>
    <metadataPolicy />
    <dataPolicy />
    <submissionPolicy />
   </eprints>
  </description>'."\n"; 
}

// If you want to point harvesters to other repositories, you can list their
// base URLs. Usage of friends container is RECOMMENDED.
// see http://www.openarchives.org/OAI/2.0/guidelines-friends.htm 
// for details

// To include, change 'false' to 'true'.
if (false) {
	$output .= 
'  <description>
   <friends xmlns="http://www.openarchives.org/OAI/2.0/friends/" 
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/friends/
            http://www.openarchives.org/OAI/2.0/friends.xsd">
    <baseURL>http://naca.larc.nasa.gov/oai2.0/</baseURL>
    <baseURL>http://techreports.larc.nasa.gov/ltrs/oai2.0/</baseURL>
    <baseURL>http://physnet.uni-oldenburg.de/oai/oai2.php</baseURL>
    <baseURL>http://cogprints.soton.ac.uk/perl/oai</baseURL>
    <baseURL>http://ub.uni-duisburg.de:8080/cgi-oai/oai.pl</baseURL>
    <baseURL>http://rocky.dlib.vt.edu/~jcdlpix/cgi-bin/OAI1.1/jcdlpix.pl</baseURL>
   </friends>
  </description>'."\n"; 
}

// If you want to provide branding information, adjust accordingly.
// Usage of friends container is OPTIONAL.
// see http://www.openarchives.org/OAI/2.0/guidelines-branding.htm 
// for details

// To include, change 'false' to 'true'.
if (false) {
	$output .= 
'  <description>
   <branding xmlns="http://www.openarchives.org/OAI/2.0/branding/"
             xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
             xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/branding/
             http://www.openarchives.org/OAI/2.0/branding.xsd">
    <collectionIcon>
     <url>http://my.site/icon.png</url>
     <link>http://my.site/homepage.html</link>
     <title>MySite(tm)</title>
     <width>88</width>
     <height>31</height>
    </collectionIcon>
    <metadataRendering 
     metadataNamespace="http://www.openarchives.org/OAI/2.0/oai_dc/" 
     mimeType="text/xsl">http://some.where/DCrender.xsl</metadataRendering>
    <metadataRendering
     metadataNamespace="http://another.place/MARC" 
     mimeType="text/css">http://another.place/MARCrender.css</metadataRendering>
   </branding>
  </description>'."\n";
}

if(strlen($output)>10) {
	$des = $outputObj->doc->createDocumentFragment();
	$des->appendXML($output);
	$outputObj->verbNode->appendChild($des);
}
?>
