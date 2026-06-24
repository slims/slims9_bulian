<?php
/**
 * \file
 * \brief Utilities for the OAI Data Provider
 *
 * A collection of functions used.
 */

/** Dump information of a varible for debugging,
 * only works when SHOW_QUERY_ERROR is true.
 * \param $var_name Type: string Name of variable is being debugded
 * \param $var Type: mix Any type of varibles used in PHP
 * \see SHOW_QUERY_ERROR in oaidp-config.php
 */
function debug_var_dump($var_name, $var) {
	if (SHOW_QUERY_ERROR) {
		echo "Dumping \${$var_name}: \n";
		var_dump($var)."\n";
	}
} 

/** Prints human-readable information about a variable for debugging,
 * only works when SHOW_QUERY_ERROR is true.
 * \param $var_name Type: string Name of variable is being debugded
 * \param $var Type: mix Any type of varibles used in PHP
 * \see SHOW_QUERY_ERROR in oaidp-config.php
 */
function debug_print_r($var_name, $var) {
	if (SHOW_QUERY_ERROR) {
		echo "Printing \${$var_name}: \n";
		print_r($var)."\n";
	}
} 

/** Prints a message for debugging,
 * only works when SHOW_QUERY_ERROR is true.
 * PHP function print_r can be used to construct message with <i>return</i> parameter sets to true.
 * \param $msg Type: string Message needs to be shown
 * \see SHOW_QUERY_ERROR in oaidp-config.php
 */
function debug_message($msg) {
	if (!SHOW_QUERY_ERROR) return;
	echo $msg,"\n";
}

/** Check if provided correct arguments for a request.
 *
 * Only number of parameters is checked.
 * metadataPrefix has to be checked before it is used.
 * set has to be checked before it is used.
 * resumptionToken has to be checked before it is used.
 * from and until can easily checked here because no extra information 
 * is needed.
 */
function checkArgs($args, $checkList) {
//	global $errors, $TOKEN_VALID, $METADATAFORMATS;
	global $errors,  $METADATAFORMATS;
//	$verb = $args['verb'];
	unset($args["verb"]);

	debug_print_r('checkList',$checkList);
	debug_print_r('args',$args);
	
	// "verb" has been checked before, no further check is needed
	if(isset($checkList['required'])) {
		for($i = 0; $i < count($checkList["required"]); $i++) {
			debug_message("Checking: par$i: ". $checkList['required'][$i] . " in ");
			debug_var_dump("isset(\$args[\$checkList['required'][\$i]])",isset($args[$checkList['required'][$i]]));
			// echo "key exists". array_key_exists($checkList["required"][$i],$args)."\n";
			if(isset($args[$checkList['required'][$i]])==false) {
			// echo "caught\n";
				$errors[] = oai_error('missingArgument', $checkList["required"][$i]);
			} else {
				// if metadataPrefix is set, it is in required section
				if(isset($args['metadataPrefix'])) {
					$metadataPrefix = $args['metadataPrefix'];
					// Check if the format is supported, it has enough infor (an array), last if a handle has been defined.
					if (!array_key_exists ($metadataPrefix, $METADATAFORMATS) || !(is_array($METADATAFORMATS[$metadataPrefix])
						|| !isset($METADATAFORMATS[$metadataPrefix]['myhandler']))) {
							$errors[] = oai_error('cannotDisseminateFormat', 'metadataPrefix', $metadataPrefix);
					}
				}
				unset($args[$checkList["required"][$i]]);
			}
		}
	}
	debug_message('Before return');
	debug_print_r('errors',$errors);
	if (!empty($errors)) return;

	// check to see if there is unwanted	
	foreach($args as $key => $val) {
		debug_message("checkArgs: $key");
		if(!in_array($key, $checkList["ops"])) {
			debug_message("Wrong\n".print_r($checkList['ops'],true)); 
			//$errors[] = oai_error('badArgument', $key, $val); // ignore it...
		}
		switch ($key) { 
			case 'from':
			case 'until':
				if(!checkDateFormat($val)) {
					$errors[] = oai_error('badGranularity', $key, $val); 
				}
			break;
			
			case 'resumptionToken':
			if (!is_valid_resumption_token($val)) {
					$errors[] = oai_error('badResumptionToken');
			}
			break;		
		}
	}
}

/** Validates an identifier. The pattern is: '/^[-a-z\.0-9]+$/i' which means 
 * it accepts -, letters and numbers. 
 * Used only by function <B>oai_error</B> code idDoesNotExist. 
 * \param $url Type: string
 */
function is_valid_uri($url)
{
	return((bool)preg_match('/^[-a-z\.0-9]+$/i', $url));
}

/** Validates attributes come with the query.
 * It accepts letters, numbers, ':', '_', '.' and -. 
 * Here there are few more match patterns than is_valid_uri(): ':_'.
 * \param $attrb Type: string
 */
function is_valid_attrb($attrb) {
	return preg_match("/^[_a-zA-Z0-9\-\:\.\+\>\%]+$/",$attrb);
}

/** Validates signed OAI resumption tokens before they are decoded. */
function is_valid_resumption_token($token) {
	$token = urldecode((string)$token);
	return $token !== ''
		&& strlen($token) <= 1024
		&& preg_match('/^[A-Za-z0-9_-]+\.[a-f0-9]{64}$/i', $token);
}

function is_valid_oai_metadata_prefix($metadataPrefix) {
	return is_string($metadataPrefix)
		&& preg_match('/^[A-Za-z0-9_.-]{1,64}$/', $metadataPrefix);
}

function is_valid_oai_set($set) {
	return is_string($set)
		&& preg_match('/^[A-Za-z0-9_.:-]{1,128}$/', $set);
}

function is_valid_oai_filter_date($date) {
	return is_string($date)
		&& preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $date);
}
 
/** All datestamps used in this system are GMT even
 * return value from database has no TZ information
 */
function formatDatestamp($datestamp)
{
	return date("Y-m-d\TH:i:s\Z",strtotime($datestamp));
}

/** The database uses datastamp without time-zone information.
 * It needs to clean all time-zone informaion from time string and reformat it
 */
function checkDateFormat($date) {
	$date = str_replace(array("T","Z")," ",urldecode($date));
	$time_val = strtotime($date);
    if (SHOW_QUERY_ERROR) { echo "timeval: $time_val\n"; }
	if(!$time_val) return false;
	if(strstr($date,":")) {
		return date("Y-m-d H:i:s",$time_val);
	} else {
		return date("Y-m-d",$time_val);
	}
}

/** Retrieve all defined 'setSpec' from configuraiton of $SETS. 
 * It is used by ANDS_TPA::create_obj_node();
*/
function prepare_set_names() {
	global $SETS;
	$n = count($SETS);
	$a = array_fill(0,$n,'');
	for ($i = 0; $i <$n; $i++) {
		$a[$i] = $SETS[$i]['setSpec'];
	}
	return $a;
}

/** Finish a request when there is an error: send back errors. */
function oai_exit()
{
//	global $CONTENT_TYPE;
	header(CONTENT_TYPE);
	global $args,$errors,$compress;
	$e = new ANDS_Error_XML($args,$errors);
	if ($compress) {
		ob_start('ob_gzhandler');
	}
	
	$e->display();

	if ($compress) {
		ob_end_flush();
	}
	
	exit();
}

// ResumToken section
/** Generate a string based on the current Unix timestamp in microseconds for creating resumToken file name. */
function get_token()
{
	list($usec, $sec) = explode(" ", microtime());
	return ((int)($usec*1000) + (int)($sec*1000));
}

/** Create a token file. 
 * It has three parts which is separated by '#': cursor, extension of query, metadataPrefix.
 * Called by listrecords.php.
 */
function createResumToken($cursor, $filters, $metadataPrefix) {

	/*
	$token = get_token(); 
	$fp = fopen (TOKEN_PREFIX.$token, 'w');
	if($fp==false) { 
		exit("Cannot write. Writer permission needs to be changed.");
	}	
	fputs($fp, "$cursor#"); 
	fputs($fp, "$extquery#"); 
	fputs($fp, "$metadataPrefix#"); 
	fclose($fp);
	return $token; 
	*/

	$filters = normalizeOaiFilters($filters);
	if ($filters === false || !is_valid_oai_metadata_prefix($metadataPrefix)) {
		return '';
	}

	$payload = array(
		'v' => 2,
		'c' => max(0, (int)$cursor),
		'f' => $filters,
		'm' => $metadataPrefix,
		't' => time()
	);
	$encodedPayload = oai_base64url_encode(json_encode($payload));
	$signature = hash_hmac('sha256', $encodedPayload, oai_token_secret());

	return $encodedPayload . '.' . $signature;
}

/** Read a saved ResumToken */
function readResumToken($resumptionToken) {

	/*
	$rtVal = false;
	$fp = fopen($resumptionToken, 'r');
	if ($fp!=false) {
		$filetext = fgets($fp, 255);
		$textparts = explode('#', $filetext);
		fclose($fp); 
		unlink ($resumptionToken);
		$rtVal = array((int)$textparts[0], $textparts[1], $textparts[2]);
	} 
	return $rtVal; 
	*/

	$resumptionToken = urldecode((string)$resumptionToken);
	if (!is_valid_resumption_token($resumptionToken)) {
		return false;
	}

	list($encodedPayload, $signature) = explode('.', $resumptionToken, 2);
	$expectedSignature = hash_hmac('sha256', $encodedPayload, oai_token_secret());
	if (!hash_equals($expectedSignature, $signature)) {
		return false;
	}

	$decodedPayload = oai_base64url_decode($encodedPayload);
	if ($decodedPayload === false) {
		return false;
	}

	$payload = json_decode($decodedPayload, true);
	if (!is_array($payload)
		|| !isset($payload['v'], $payload['c'], $payload['f'], $payload['m'], $payload['t'])
		|| (int)$payload['v'] !== 2
		|| !is_numeric($payload['c'])
		|| (int)$payload['c'] < 0
		|| !is_numeric($payload['t'])
		|| !is_valid_oai_metadata_prefix($payload['m'])) {
		return false;
	}

	$issuedAt = (int)$payload['t'];
	$tokenValidSeconds = defined('TOKEN_VALID') ? TOKEN_VALID : 24*3600;
	if ($issuedAt > time() + 300 || $issuedAt + $tokenValidSeconds < time()) {
		return false;
	}

	$filters = normalizeOaiFilters($payload['f']);
	if ($filters === false) {
		return false;
	}

	return array((int)$payload['c'], $filters, $payload['m']);
}

function oai_base64url_encode($data) {
	return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function oai_base64url_decode($data) {
	if (!is_string($data) || !preg_match('/^[A-Za-z0-9_-]+$/', $data)) {
		return false;
	}

	$padding = strlen($data) % 4;
	if ($padding > 0) {
		$data .= str_repeat('=', 4 - $padding);
	}

	return base64_decode(strtr($data, '-_', '+/'), true);
}

function oai_token_secret() {
	$parts = array(
		defined('DB_HOST') ? DB_HOST : '',
		defined('DB_NAME') ? DB_NAME : '',
		defined('DB_USERNAME') ? DB_USERNAME : '',
		defined('DB_PASSWORD') ? DB_PASSWORD : '',
		defined('SLIMS_SERVER_NAME') ? SLIMS_SERVER_NAME : '',
		defined('TOKEN_PREFIX') ? TOKEN_PREFIX : '',
		__FILE__
	);

	return hash('sha256', implode('|', $parts));
}

function normalizeOaiFilters($filters) {
	if (!is_array($filters)) {
		return false;
	}

	$normalized = array();
	$allowedKeys = array('from', 'until', 'set');
	foreach ($filters as $key => $value) {
		if (!in_array($key, $allowedKeys)) {
			return false;
		}

		$value = urldecode((string)$value);
		if ($key === 'set') {
			if (!is_valid_oai_set($value)) {
				return false;
			}
			$normalized[$key] = $value;
			continue;
		}

		if (!is_valid_oai_filter_date($value)) {
			return false;
		}
		$normalized[$key] = $value;
	}

	return $normalized;
}

function buildOaiFilterQuery($filters, &$params) {
	global $SQL;

	$params = array();
	$filters = normalizeOaiFilters($filters);
	if ($filters === false) {
		return false;
	}

	$query = '';
	if (isset($filters['from'])) {
		$query .= ' AND ' . $SQL['datestamp'] . ' >= :oai_from';
		$params[':oai_from'] = $filters['from'];
	}

	if (isset($filters['until'])) {
		$query .= ' AND ' . $SQL['datestamp'] . ' <= :oai_until';
		$params[':oai_until'] = $filters['until'];
	}

	if (isset($filters['set'])) {
		$set = $filters['set'];
		if (strstr($set, 'class:')) {
			$set = substr($set, 6);
		}
		$query .= ' AND ' . $SQL['set'] . ' LIKE :oai_set';
		$params[':oai_set'] = '%' . $set . '%';
	}

	return $query;
}

// Here are a couple of queries which might need to be adjusted to 
// your needs. Normally, if you have correctly named the columns above,
// this does not need to be done.

/** this function should generate a query which will return
 * all records
 * the useless condition id_column = id_column is just there to ease
 * further extensions to the query, please leave it as it is.
 */
function selectallQuery ($metadPrefix = "oai_dc", $id = '', $extselect = [])
{
	global $SQL;

	$str_extselect = !empty($extselect) ? ', ' . implode(', ', $extselect) : '';
	$query = "SELECT " . $SQL['identifier'] . "," . $SQL['datestamp'] . "," . $SQL['set'] . $str_extselect .
		" FROM ".$SQL['table'] . " WHERE 1 ";
	if ($id != '') {
		$query .= " AND ".$SQL['identifier']." ='$id'";
	}
	return $query;
}

/** this function will return metadataFormat of a record */
function idFormatQuery($id)
{
	global $SQL;
	return 'select '.$SQL['metadataPrefix'].' FROM '.$SQL['table']. " WHERE ".$SQL['identifier']." = '".$id."'";
}

/** this function will return identifier and datestamp for all records
 * not very useful
 */
function idQuery ($metadPrefix = "rif", $id = '')
{
	global $SQL;

	if ($SQL['set'] != '') {
		$query = 'select '.$SQL['identifier'].','.$SQL['datestamp'].','.$SQL['set'].' FROM '.$SQL['table']. " WHERE ".$SQL['metadataPrefix']." LIKE '%$metadPrefix%'";
	} else {
		$query = 'select '.$SQL['identifier'].','.$SQL['datestamp'].' FROM '.$SQL['table']. " WHERE ".$SQL['metadataPrefix']." LIKE '%$metadPrefix%'";
	}
	
	if ($id != '') {
		$query .= " AND ".$SQL['identifier']." = '$id'";
	}

	return $query;
}

/** filter for until, appends to the end of SQL query */
function untilQuery($until) 
{
	global $SQL;

	return ' AND '.$SQL['datestamp']." <= '$until'";
}

/** filter for from , appends to the end of SQL query */
function fromQuery($from)
{
	global $SQL;

	return ' AND '.$SQL['datestamp']." >= '$from'";
}

/** filter for sets,  appends to the end of SQL query */
function setQuery($set)
{
	global $SQL;
	// strip off "class:" which is not saved in database
	if(strstr($set,"class:")) $set = substr($set,6);
	return ' AND '.$SQL['set']." LIKE '%$set%'";
}

/** for accurately to assess how many records satisfy conditions for all DBs */
function rowCount($metadataPrefix, $filters, $db) {
	global $SQL;
	$n = 0;
	$params = array();
	$extQuery = buildOaiFilterQuery($filters, $params);
	if ($extQuery === false) {
		return $n;
	}

	$sql = "SELECT COUNT(*) FROM ".$SQL['table'] . " WHERE 1 " . $extQuery;
	if ($res = $db->prepare($sql)) {
		$res->execute($params);
		$n = $res->fetchColumn();
	}
	return $n;
}

/** A worker function for processing an error when a query was executed
 * \param $query string, original query
 * \param $e PDOException, the PDOException object
*/
function process_pdo_error($query, $e) {
			echo $query.' was failed\n';
			echo $e->getMessage();
}

/** When query return no result, throw an Exception of Not found.
 * \param $db PDO
 * \param $query string
 * \return $res PDOStatement
 */
function exec_pdo_query($db, $query)
{
	$res = $db->query($query);
	if ($res===false) {
		throw new Exception($query.":\nIt found nothing.\n");			
	} else return $res;
}			
?>
