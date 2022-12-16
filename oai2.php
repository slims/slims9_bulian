<?php
/**
 * \file oai2.php
 * \brief
 * OAI Data Provider command processor
 *
 * OAI Data Provider is not designed for human to retrieve data.
 *
 * This is an implementation of OAI Data Provider version 2.0.
 * @see http://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm
 * 
 * It needs other files:
 * - oaidp-config.php : Configuration of provider
 * - oaidp-util.php : Utility functions
 * - xml_creater.php : XML generating functions
 * - Actions:
 * 	- identify.php : About the provider
 * 	- listmetadataformats.php : List supported metadata formats
 * 	- listrecords.php : List identifiers and records
 * 	- listsets.php : List sets
 * 	- getrecord.php : Get a record
 *		- Your own implementation for providing metadata records.
 *
 * It also initiates:
 *	- PDO datbase connection object $db.
 *	- ANDS_XML XML document handler $outputObj.  
 *
 * \todo <b>Remember:</b> to define your own classess for generating metadata records.
 * In common cases, you have to implement your own code to act fully and correctly.
 * For generic usage, you can try the ANDS_Response_XML defined in xml_creater.php.
 */
 
// Report all errors except E_NOTICE
// This is the default value set in php.ini
// If anything else, try them.
// error_reporting (E_ALL ^ E_NOTICE);

/**
 * An array for collecting erros which can be reported later. It will be checked before a new action is taken.
 */
$errors = array();

ini_set('session.use_cookies', '0');

// key to authenticate
define('INDEX_AUTH', '1');

// required file
require 'sysconfig.inc.php';
define('OAI_LIB', realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.
	'lib'.DIRECTORY_SEPARATOR.
	'oaipmh'.DIRECTORY_SEPARATOR);

require_once(OAI_LIB.'oaidp-config.php');
require_once(OAI_LIB.'oaidp-util.php');
require_once(OAI_LIB.'ands_tpa.php');


/**
 * Supported attributes associate to verbs.
 */
$attribs = array ('from', 'identifier', 'metadataPrefix', 'set', 'resumptionToken', 'until');

if (in_array($_SERVER['REQUEST_METHOD'],array('GET','POST'))) {
		$args = $_REQUEST;
} else {
	$errors[] = oai_error('badRequestMethod', $_SERVER['REQUEST_METHOD']);
}


// Always using htmlentities() function to encodes the HTML entities submitted by others.
// No one can be trusted.
foreach ($args as $key => $val) {
	$checking = urlencode(stripslashes($val));
	if (!is_valid_attrb($checking)) {
		$errors[] = oai_error('badArgument', $checking);
	} else {$args[$key] = $checking; }
}
if (!empty($errors)) {	oai_exit(); }

foreach($attribs as $val) {
	unset($$val);
}



// Create a PDO object
$db = \SLiMS\DB::getInstance();

// For generic usage or just trying:
// require_once('xml_creater.php');
// In common cases, you have to implement your own code to act fully and correctly.


// Default, there is no compression supported
$compress = FALSE;
if (isset($compression) && is_array($compression)) {
	if (in_array('gzip', $compression) && ini_get('output_buffering')) {
		$compress = TRUE;
	}
}

if (SHOW_QUERY_ERROR) {
	echo "Args:\n"; print_r($args);
}

if (isset($args['verb'])) {
	switch ($args['verb']) {

		case 'Identify':
			// we never use compression in Identify
			$compress = FALSE;
			if(count($args)>1) {
				foreach($args as $key => $val) {
					if(strcmp($key,"verb")!=0) {
						$errors[] = oai_error('badArgument', $key, $val);
					}	
				}
			}
			if (empty($errors)) include OAI_LIB.'identify.php';
			break;

		case 'ListMetadataFormats':
			$checkList = array("ops"=>array("identifier"));
			checkArgs($args, $checkList);
			if (empty($errors)) include OAI_LIB.'listmetadataformats.php';
			break;

		case 'ListSets':
			if(isset($args['resumptionToken']) && count($args) > 2) {
					$errors[] = oai_error('exclusiveArgument');
			}
			$checkList = array("ops"=>array("resumptionToken"));
			checkArgs($args, $checkList);
			if (empty($errors)) include OAI_LIB.'listsets.php';
			break;

		case 'GetRecord':
			$checkList = array("required"=>array("metadataPrefix","identifier"));
			checkArgs($args, $checkList);
			if (empty($errors)) include OAI_LIB.'getrecord.php';
			break;

		case 'ListIdentifiers':
		case 'ListRecords':
			if(isset($args['resumptionToken'])) {
				if (count($args) > 2) {
					$errors[] = oai_error('exclusiveArgument');
				}
				$checkList = array("ops"=>array("resumptionToken"));
			} else {
				$checkList = array("required"=>array("metadataPrefix"),"ops"=>array("from","until","set"));
			}
			checkArgs($args, $checkList);
			if (empty($errors)) include OAI_LIB.'listrecords.php';
			break;

		default:
			// we never use compression with errors
			$compress = FALSE;
			$errors[] = oai_error('badVerb', $args['verb']);
	} /*switch */
} else {
	$errors[] = oai_error('noVerb');
}

if (!empty($errors)) {	oai_exit(); }

if ($compress) {
	ob_start('ob_gzhandler');
}

header(CONTENT_TYPE);

if(isset($outputObj)) {
	$outputObj->display();
} else {
	exit("There is a bug in codes");
}

	if ($compress) {
		ob_end_flush();
	}

?>
