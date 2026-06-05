<?php
/**
 * \file
 * \brief Response to Verb ListRecords
 *
 * Lists records according to conditions. If there are too many, a resumptionToken is generated.
 * - If a request comes with a resumptionToken and is still valid, read it and send back records.
 * - Otherwise, set up a query with conditions such as: 'metadataPrefix', 'from', 'until', 'set'.
 * Only 'metadataPrefix' is compulsory.  All conditions are accessible through global array variable <B>$args</B>  by keywords.
 */

debug_message("\nI am debuging". __FILE__) ;

// Resume previous session?
if (isset($args['resumptionToken'])) {
	$readings = readResumToken($args['resumptionToken']);            

	if ($readings == false) {
		$errors[] = oai_error('badResumptionToken', '', $args['resumptionToken']);
	} else {
		debug_var_dump('readings',$readings);
		list($deliveredrecords, $filters, $metadataPrefix) = $readings;
		$deliveredrecords = max(0, (int)$deliveredrecords);
	}
} else { // no, we start a new session
	$deliveredrecords = 0; 
	$filters = array();

	$metadataPrefix = $args['metadataPrefix'];

	if (isset($args['from'])) {
		$from = checkDateFormat($args['from']);
		if ($from === false) {
			$errors[] = oai_error('badGranularity', 'from', $args['from']);
		} else {
			$filters['from'] = $from;
		}
	}

	if (isset($args['until'])) {
		$until = checkDateFormat($args['until']);
		if ($until === false) {
			$errors[] = oai_error('badGranularity', 'until', $args['until']);
		} else {
			$filters['until'] = $until;
		}
	}

	if (isset($args['set'])) {
		if (is_array($SETS)) {
			$set = urldecode($args['set']);
			if (!is_valid_oai_set($set)) {
				$errors[] = oai_error('badArgument', 'set', $args['set']);
			} else {
				$filters['set'] = $set;
			}
		} else {
			$errors[] = oai_error('noSetHierarchy'); 
		}
	}
}

if (!empty($errors)) {
	oai_exit();
}

// Load the handler
if (isset($METADATAFORMATS[$metadataPrefix])
	&& is_array($METADATAFORMATS[$metadataPrefix])
	&& isset($METADATAFORMATS[$metadataPrefix]['myhandler'])) {
	$inc_record  = $METADATAFORMATS[$metadataPrefix]['myhandler'];
	include($inc_record);
} else {
	$errors[] = oai_error('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
}

if (!empty($errors)) {
	oai_exit();
}

if (empty($errors)) {
	$params = array();
	$extquery = buildOaiFilterQuery($filters, $params);
	if ($extquery === false) {
		$errors[] = oai_error('badArgument', 'filter');
	}
}

if (empty($errors)) {
	$query = selectallQuery($metadataPrefix, '', ['opac_hide']) . $extquery . " ORDER BY " . $SQL['identifier'] . " ASC ";

	// workaround for mysql
	if (isset($deliveredrecords)){
		$query .= " LIMIT " . (int)MAXRECORDS . " OFFSET " . (int)$deliveredrecords . " ";
	}
	debug_message("Query: $query") ;

	$res = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$r = $res->execute($params);
 	if ($r===false) {
		if (SHOW_QUERY_ERROR) {
			echo __FILE__.','.__LINE__."<br />";
			echo "Query: $query<br />\n";
			print_r($db->errorInfo());
			exit();
		} else {
			$errors[] = oai_error('noRecordsMatch');
		}		
	} else {
		$r = $res->setFetchMode(PDO::FETCH_ASSOC);
		if ($r===false) {
			exit("FetchMode is not supported");
		}
		$num_rows = rowCount($metadataPrefix, $filters, $db);
		if ($num_rows==0) {
			#echo "Cannot find records: $query\n"; #debug
			$errors[] = oai_error('noRecordsMatch');
		}
	}
}

if (!empty($errors)) {
	oai_exit();
}

// Will we need a new ResumptionToken?
if($args['verb']=='ListRecords') {
	$maxItems = MAXRECORDS;
} elseif($args['verb']=='ListIdentifiers') {
	$maxItems = MAXIDS;
} else {
	exit("Check ".__FILE__." ".__LINE__.", there is something wrong.");
}
$maxrec = min($num_rows - $deliveredrecords, $maxItems);

if ($num_rows - $deliveredrecords > $maxItems) {
	$cursor = (int)$deliveredrecords + $maxItems;
	$restoken = createResumToken($cursor, $filters, $metadataPrefix);
	$expirationdatetime = DateTimeImmutable::createFromFormat('U', time()+TOKEN_VALID)->format('Y-m-dTTZ');
}
// Last delivery, return empty ResumptionToken
elseif (isset($args['resumptionToken'])) {
	$restoken = $args['resumptionToken']; // just used as an indicator
	unset($expirationdatetime);
}


// this don't work on mysql
/*
if (isset($args['resumptionToken'])) {
	debug_message("Try to resume because a resumptionToken supplied.") ;
	$record = $res->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $deliveredrecords); 
}
*/

// Record counter
$countrec  = 0;

// Publish a batch to $maxrec number of records
$outputObj = new ANDS_Response_XML($args);
while ($countrec++ < $maxrec) {
	$record = $res->fetch(PDO::FETCH_ASSOC);
	//print_r($record);
	if ($record===false) {
		if (SHOW_QUERY_ERROR) {
			echo __FILE__.",". __LINE__."<br />";
			print_r($db->errorInfo());
			exit();
		}
	}

	$identifier = $record[$SQL['identifier']];
	$datestamp = formatDatestamp($record[$SQL['datestamp']]);
	$setspec = $record[$SQL['set']];

	// debug_var_dump('record', $record);
	if (!isset($record[$SQL['deleted']]) && $record['opac_hide'] > 0) $record[$SQL['deleted']] = true;
	if (isset($record[$SQL['deleted']]) && ($record[$SQL['deleted']] === true) &&
		($deletedRecord == 'transient' || $deletedRecord == 'persistent')) {
		$status_deleted = TRUE;
	} else {
		$status_deleted = FALSE;
	}
	
  //debug_var_dump('status_deleted', $status_deleted);
	if($args['verb']=='ListRecords') {
		$cur_record = $outputObj->create_record();
		$cur_header = $outputObj->create_header($oaiprefix.'-'.$identifier, $datestamp,$setspec,$cur_record);
	// return the metadata record itself
		if (!$status_deleted) {
			debug_var_dump('inc_record',$inc_record);
			create_metadata($outputObj, $cur_record, $identifier, $setspec, $db);
		}	
	} else { // for ListIdentifiers, only identifiers will be returned.
		$cur_header = $outputObj->create_header($oaiprefix.'-'.$identifier, $datestamp,$setspec);
	}
	if ($status_deleted) {
		$cur_header->setAttribute("status","deleted");
	}  
}

// ResumptionToken
if (isset($restoken)) {
	if(isset($expirationdatetime)) {
		$outputObj->create_resumpToken($restoken,$expirationdatetime,$num_rows,$cursor); 
	} else {
		$outputObj->create_resumpToken('',null,$num_rows,$deliveredrecords); 
	}	
}

// end ListRecords
if (SHOW_QUERY_ERROR) {echo "Debug listrecord.php reached to the end.\n\n";}
?>
