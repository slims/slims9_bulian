<?php
/**
 * \file
 * \brief Response to Verb ListMetadataFormats
 *
 * The information of supported metadata formats is saved in database and retrieved by calling function <B>idFormatQuery</B>.
 * \sa idFormatQuery
 */

/**
 * Add a metadata format node to an ANDS_Response_XML
 * \param &$outputObj
 *	type: ANDS_Response_XML. The ANDS_Response_XML object for output.
 * \param $key
 * 	type string. The name of new node.
 * \param $val
 * 	type: array. Values accessable through keywords 'schema' and 'metadataNamespace'.
 *
 */
function addMetedataFormat(&$outputObj,$key,$val) {
	$cmf = $outputObj->add2_verbNode("metadataFormat");
	$outputObj->addChild($cmf,'metadataPrefix',$key);
	$outputObj->addChild($cmf,'schema',$val['schema']);
	$outputObj->addChild($cmf,'metadataNamespace',$val['metadataNamespace']);
}

if (isset($args['identifier'])) {
	$identifier = $args['identifier'];
	$query = idFormatQuery($identifier);
	$res = $db->query($query);
 	if ($res==false) {
		if (SHOW_QUERY_ERROR) {
			echo __FILE__.','.__LINE__."<br />";
			echo "Query: $query<br />\n";
			die($db->errorInfo());
		} else {
			$errors[] = oai_error('idDoesNotExist','', $identifier);
		}
	} else {
		$record = $res->fetch();
		if($record===false) {
			$errors[] = oai_error('idDoesNotExist', '', $identifier);
		} else {
			$mf = explode(",",$record[$SQL['metadataPrefix']]);    
		}
	}
}

//break and clean up on error
if (!empty($errors)) oai_exit();

$outputObj = new ANDS_Response_XML($args);
if (isset($mf)) {
	foreach($mf as $key) {
		$val = $METADATAFORMATS[$key];
		addMetedataFormat($outputObj,$key, $val);
	}
} elseif (is_array($METADATAFORMATS)) {
		foreach($METADATAFORMATS as $key=>$val) {
			addMetedataFormat($outputObj,$key, $val);
		}
}
else { // a very unlikely event
	$errors[] = oai_error('noMetadataFormats'); 
	oai_exit();
}
?>
