<?php
/**
 * \file
 * \brief functions and class related to generating XML response file.
 *
 * Example usage:
 *
 * \code
 * $par_array = array("verb"=>"ListRecords","resumptionToken"=>"9CD1DA87F59C3E960871F4F3C9D093887C17D174");    
 * // Example 1: Error response
 * $error_array[] = oai_error("badVerb","Rubish");
 * $error_array[] = oai_error("sameVerb");
 * $e = new ANDS_Error_XML($par_array,$error_array);
 * $e->display();
 *
 * // Example 2: Normal response without error codes
 * $par_array = array("verb"=>"ListRecords","resumptionToken"=>"9CD1DA87F59C3E960871F4F3C9D093887C17D174");
 * $test = new ANDS_Response_XML($par_array);
 * $record_node = $test->create_record();
 * $test->create_header("function: identifier string",gmdate("Y-m-d\TH:i:s\Z"),"collection",$record_node);
 * $test->create_metadata($record_node);
 * $test->display();
 * \endcode
 *
 * \see http://www.openarchives.org/OAI/openarchivesprotocol.html#ErrorConditions
 */

/*
http://www.openarchives.org/OAI/openarchivesprotocol.html#ErrorConditions

badArgument:
	The request includes illegal arguments, is missing required arguments, includes a repeated argument, or values for arguments have an illegal syntax. Applied to all verbs.

badResumptionToken:
	The value of the resumptionToken argument is invalid or expired. 	Applied to: ListIdentifiers, ListRecords, ListSets

badVerb:
	Value of the verb argument is not a legal OAI-PMH verb, the verb argument is missing, or the verb argument is repeated. 	N/A

cannotDisseminateFormat:
	The metadata format identified by the value given for the metadataPrefix argument is not supported by the item or by the repository. 	Applied to GetRecord, ListIdentifiers, ListRecords

idDoesNotExist:
	The value of the identifier argument is unknown or illegal in this repository. 	Applied to GetRecord, ListMetadataFormats

noRecordsMatch:
	The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list. Applied to	ListIdentifiers, ListRecords
	
noMetadataFormats: 
	There are no metadata formats available for the specified item. 	Applied to ListMetadataFormats.
	
noSetHierarchy: 
	The repository does not support sets. Applied to ListSets, ListIdentifiers, ListRecords

*/

/** utility funciton to mapping error codes to readable messages */
function oai_error($code, $argument = '', $value = '')
{
	switch ($code) {
		case 'badArgument' :
			$text = "The argument '$argument' (value='$value') included in the request is not valid.";
			break;

		case 'badGranularity' :
			$text = "The value '$value' of the argument '$argument' is not valid.";
			$code = 'badArgument';
			break;

		case 'badResumptionToken' :
			$text = "The resumptionToken '$value' does not exist or has already expired.";
			break;

		case 'badRequestMethod' :
			$text = "The request method '$argument' is unknown.";
			$code = 'badVerb';
			break;

		case 'badVerb' :
			$text = "The verb '$argument' provided in the request is illegal.";
			break;

		case 'cannotDisseminateFormat' :
			$text = "The metadata format '$value' given by $argument is not supported by this repository.";
			break;

		case 'exclusiveArgument' :
			$text = 'The usage of resumptionToken as an argument allows no other arguments.';
			$code = 'badArgument';
			break;

		case 'idDoesNotExist' :
			$text = "The value '$value' of the identifier does not exist in this repository.";
			if (!is_valid_uri($value)) {
				$code = 'badArgument';
				$text .= ' Invalidated URI has been detected.';
			}
			break;

		case 'missingArgument' :
			$text = "The required argument '$argument' is missing in the request.";
			$code = 'badArgument';
			break;

		case 'noRecordsMatch' :
			$text = 'The combination of the given values results in an empty list.';
			break;

		case 'noMetadataFormats' :
			$text = 'There are no metadata formats available for the specified item.';
			break;

		case 'noVerb' :
			$text = 'The request does not provide any verb.';
			$code = 'badVerb';
			break;

		case 'noSetHierarchy' :
			$text = 'This repository does not support sets.';
			break;

		case 'sameArgument' :
			$text = 'Do not use the same argument more than once.';
			$code = 'badArgument';
			break;

		case 'sameVerb' :
			$text = 'Do not use verb more than once.';
			$code = 'badVerb';
			break;

		case 'notImp' :
			$text = 'Not yet implemented.';
			$code = 'debug';
			break;

		default:
			$text = "Unknown error: code: '$code', argument: '$argument', value: '$value'";
			$code = 'badArgument';
	}
	return $code."|".$text;
}

/**
 * A wraper of DOMDocument for data provider
 */
class ANDS_XML {
	
	public $doc; /**< Type: DOMDocument. Handle of current XML Document object */

  /**
   * Constructs an ANDS_XML object.
   *
   * @param $par_array Type: array.
   *   Array of request parameters for creating an ANDS_XML object.
   * \see create_request.
   */
  function __construct($par_array) {
  	$this->doc = new DOMDocument("1.0","UTF-8");
    //creating an xslt adding processing line
    $xslt = $this->doc->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="./oai2.xsl"');
    //adding it to the xml
    $this->doc->appendChild($xslt);
  	// oai_node equals to $this->doc->documentElement;
    $oai_node = $this->doc->createElement("OAI-PMH");
		$oai_node->setAttribute("xmlns","http://www.openarchives.org/OAI/2.0/");
		$oai_node->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
		$oai_node->setAttribute("xsi:schemaLocation","http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd");
		$this->addChild($oai_node,"responseDate",gmdate("Y-m-d\TH:i:s\Z"));
		$this->doc->appendChild($oai_node);
		$this->create_request($par_array);
  }

  /**
   * Add a child node to a parent node on a XML Doc: a worker function.
   *
   * @param $mom_node
   *   Type: DOMNode. The target node.
   *
   * @param $name
   *   Type: string. The name of child nade is being added
   *
   * @param $value
   *   Type: string. Text for the adding node if it is a text node.
   *
   * @return DOMElement $added_node
   *   The newly created node, can be used for further expansion.
   *   If no further expansion is expected, return value can be igored.
   */

	function addChild($mom_node,$name, $value='') {
		$added_node = @$this->doc->createElement($name,$value);
		$added_node = @$mom_node->appendChild($added_node);
		return $added_node;
	}


  /**
   * Create an OAI request node.
   *
   * @param $par_array Type: array
   *   The attributes of a request node. They describe the verb of the request and other associated parameters used in the request.
   * Keys of the array define attributes, and values are their content.
   */

	function create_request($par_array) {
		$request = $this->addChild($this->doc->documentElement,"request",MY_URI);
		foreach($par_array as $key => $value) {
			$request->setAttribute($key,$value);
		}
	}

  /**
   * Display a doc in a readable, well-formatted way for display or saving
   */
	function display() {
		$pr = new DOMDocument();
		$pr->preserveWhiteSpace = false;
		$pr->formatOutput = true;
		$pr->loadXML($this->doc->saveXML());
		echo $pr->saveXML();
	}
}

/**
 * Generate an XML response when a request cannot be finished
 *
 * It has only one derived member function
 */
class ANDS_Error_XML extends ANDS_XML {
  function __construct($par_array, $error_array) {
  	parent::__construct($par_array);

		$oai_node = $this->doc->documentElement;
		foreach($error_array as $e) {
			list($code, $value) = explode("|", $e);
			$node = $this->addChild($oai_node,"error",$value); 
			$node->setAttribute("code",$code);
		}
	}  
}

/**
 * Generate an XML response to a request if no error has occured
 *
 * This is the class to further develop to suits a publication need
 */
class ANDS_Response_XML extends ANDS_XML {
  public $verbNode; /**< Type: DOMElement. Verb node itself. */
  protected $verb; /**< Type: string. The verb in the request */
   
  function __construct($par_array) {
  	parent::__construct($par_array);
		$this->verb = $par_array["verb"];
		$this->verbNode = $this->addChild($this->doc->documentElement,$this->verb);
  }

/** Add direct child nodes to verb node (OAI-PMH), e.g. response to ListMetadataFormats.
 * Different verbs can have different required child nodes.  
 *  \see create_record, create_header
 * \see http://www.openarchives.org/OAI/2.0/openarchivesprotocol.htm.
 *
 * \param $nodeName Type: string. The name of appending node.
 * \param $value Type: string. The content of appending node.
 */
	function add2_verbNode($nodeName, $value=null) {
		return $this->addChild($this->verbNode,$nodeName,$value);
	}

	/**
	 * Create an empty \<record\> node. Other nodes will be appended to it later.
	 */
	function create_record() {
		return $this->add2_verbNode("record");
	}
	
	/** Headers are enclosed inside of \<record\> to the query of ListRecords, ListIdentifiers and etc. 
   *
	 * \param $identifier Type: string. The identifier string for node \<identifier\>.
	 * \param $timestamp Type: timestamp. Timestapme in UTC format for node \<datastamp\>.
	 * \param $ands_class Type: mix. Can be an array or just a string. Content of \<setSpec\>.
	 * \param $add_to_node Type: DOMElement. Default value is null. 
	 * In normal cases, $add_to_node is the \<record\> node created previously. When it is null, the newly created header node is attatched to $this->verbNode.
	 * Otherwise it will be attatched to the desired node defined in $add_to_node. 
	 */
	function create_header($identifier,$timestamp,$ands_class, $add_to_node=null) {
		if(is_null($add_to_node)) {
			$header_node = $this->add2_verbNode("header");
		} else {
			$header_node = $this->addChild($add_to_node,"header");
		}
		$this->addChild($header_node,"identifier",$identifier);
		$this->addChild($header_node,"datestamp",$timestamp);
		if (is_array($ands_class)) {
			foreach ($ands_class as $setspec) {
				$this->addChild($header_node,"setSpec",$setspec); 
			}
		} else { $this->addChild($header_node,"setSpec",$ands_class); }
		return $header_node;
	}
	
	/** Create metadata node for holding metadata. This is always added to \<record\> node.
	 *
	 * \param $mom_record_node DOMElement. A node acts as the parent node.
	 *
   * @return $meta_node Type: DOMElement. 
   *   The newly created registryObject node which will be used for further expansion.
   *   metadata node itself is maintained by internally by the Class.
   */
	function create_metadata($mom_record_node) {
		$meta_node =  $this->addChild($mom_record_node,"metadata");
		return $meta_node;
	}
	
	/** If there are too many records request could not finished a resumpToken is generated to let harvester know
	 * 
	 * \param $token Type: string. A random number created somewhere?
	 * \param $expirationdatetime Type: string. A string representing time.
	 * \param $num_rows Type: integer. Number of records retrieved.
	 * \param $cursor Type: string. Cursor can be used for database to retrieve next time.
	 */
	function create_resumpToken($token, $expirationdatetime, $num_rows, $cursor=null) {
		$resump_node = $this->addChild($this->verbNode,"resumptionToken",$token);
		if(isset($expirationdatetime)) {
			$resump_node->setAttribute("expirationDate",$expirationdatetime);
		}
		$resump_node->setAttribute("completeListSize",$num_rows);
		$resump_node->setAttribute("cursor",$cursor);
	}
}

