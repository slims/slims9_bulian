<?php
/**
 * \file
 * \brief Response to Verb GetRecord
 *
 * Retrieve a record based its identifier.
 *
 * Local variables <B>$metadataPrefix</B> and <B>$identifier</B> need to be provided through global array variable <B>$args</B> 
 * by their indexes 'metadataPrefix' and 'identifier'.
 * The reset of information will be extracted from database based those two parameters.
 */

debug_message("\nI am debuging". __FILE__) ;

$metadataPrefix = $args['metadataPrefix'];
// myhandler is a php file which will be included to generate metadata node.
// $inc_record  = $METADATAFORMATS[$metadataPrefix]['myhandler'];

if (is_array($METADATAFORMATS[$metadataPrefix]) 
	&& isset($METADATAFORMATS[$metadataPrefix]['myhandler'])) {
	$inc_record  = $METADATAFORMATS[$metadataPrefix]['myhandler'];
} else {
	$errors[] = oai_error('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
}

if (is_numeric($args['identifier'])) {
    $identifier = $args['identifier'];
    $official_identifier = $oaiprefix = "oai".$delimiter.$repositoryIdentifier.$delimiter.$idPrefix.'-'.$identifier;
} else {
    $_identifier = explode('-', $args['identifier']);
    $key = count($_identifier) - 1;
    $identifier = $_identifier[$key];
    $official_identifier = urldecode($args['identifier']);
}

$query = selectallQuery($metadataPrefix, $identifier);

debug_message("Query: $query") ;

$res = $db->query($query);

if ($res===false) {
	if (SHOW_QUERY_ERROR) {
		echo __FILE__.','.__LINE__."<br />";
		echo "Query: $query<br />\n";
		die($db->errorInfo());
	} else {
		$errors[] = oai_error('idDoesNotExist', '', $identifier); 
	}
} elseif (!$res->rowCount()) { // based on PHP manual, it might only work for some DBs
	$errors[] = oai_error('idDoesNotExist', '', $identifier); 
}

 
if (!empty($errors)) {
	oai_exit();
}

$record = $res->fetch(PDO::FETCH_ASSOC);
if ($record===false) {
	if (SHOW_QUERY_ERROR) {
		echo __FILE__.','.__LINE__."<br />";
		echo "Query: $query<br />\n";
	}
	$errors[] = oai_error('idDoesNotExist', '', $identifier);	
}

$identifier = $record[$SQL['identifier']];

$datestamp = formatDatestamp($record[$SQL['datestamp']]); 

if (isset($record[$SQL['deleted']]) && ($record[$SQL['deleted']] == 'true') && 
	($deletedRecord == 'transient' || $deletedRecord == 'persistent')) {
	$status_deleted = TRUE;
} else {
	$status_deleted = FALSE;
}

$outputObj = new ANDS_Response_XML($args);
$cur_record = $outputObj->create_record();
#$cur_header = $outputObj->create_header($identifier, $datestamp,$record[$SQL['set']],$cur_record);
$cur_header = $outputObj->create_header($official_identifier, $datestamp,$record[$SQL['set']],$cur_record);
// return the metadata record itself
if (!$status_deleted) {
	include($inc_record); // where the metadata node is generated.
	create_metadata($outputObj, $cur_record, $identifier, $record[$SQL['set']], $db);
}	else {
	$cur_header->setAttribute("status","deleted");
}  
?>
