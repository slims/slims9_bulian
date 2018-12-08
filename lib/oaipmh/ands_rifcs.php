<?php
/**
 * \file
 * \brief classes related to generating RIF-CS XML response file for ANDS.
 *
 *
 * Generate RIF-CS set records of Activity, Collection, Party.
 * - They are closely bounded to ANDS requirements, need to know the database for getting information.
 */

require_once('xml_creater.php');

/**
 * \brief For creating RIF-CS metadata to meet the requirement of ANDS.
 *
 * Class ANDS_RIFCS provides all essential functionalities for creating ANDS RIF-CS records.
 * The protected member functions are the backbone functions which can be used for creating any ANDS RIF-CS records.
 *
 */

class ANDS_RIFCS {
 /**
 	* \var $oai_pmh 
 	* Type: ANDS_Response_XML. Assigned by constructor. \see __construct
  */
	protected $oai_pmh;
	/** \var $working_node
	* Type: DOMElement. Assigned by constructor. \see __construct	
	*/
	protected $working_node;

	/**
	 * Constructor
	 *
	 * \param $ands_response_doc ANDS_Response_XML. A XML Doc acts as the parent node.
	 * \param $metadata_node DOMElement. The meta node which all subsequent nodes will be added to.
	 */
  function __construct($ands_response_doc, $metadata_node) {
		$this->oai_pmh = $ands_response_doc;
		$this->working_node = $metadata_node;
		$this->create_regObjects();
  }

  /**
   * A worker function for easily adding a newly created node to current XML Doc.
   * @param $mom_node Type: DOMElement. Node the new child will be attached to.
   * @param $name Type: sting. The name of the child node is being added.
   * @param $value Type: sting. The text content of the child node is being added. The default is ''.
   * @return DOMElement. The added child node
   */  
	protected function addChild($mom_node,$name, $value='') {
  	return $this->oai_pmh->addChild($mom_node,$name, $value); 
  }

	/** Create a registryObjects node to hold individual registryObject's.
	 *  This is only a holder node.
	*/
	protected 	function create_regObjects() {
		$this->working_node = $this->oai_pmh->addChild($this->working_node,'registryObjects');
		$this->working_node->setAttribute('xmlns',"http://ands.org.au/standards/rif-cs/registryObjects");
		$this->working_node->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
		$this->working_node->setAttribute('xsi:schemaLocation','http://ands.org.au/standards/rif-cs/registryObjects http://services.ands.org.au/documentation/rifcs/1.2.0/schema/registryObjects.xsd');
	}
	
	/** Create a single registryObject node. Each set has its own structure but they all have an attribute of group, a key node and an originatingSource node. The newly created node will be used as the working node.
	 *
	 * \param $group string, group attribute of the new registryObject node .
	 * \param $key string, key node, used as an identifier.
	 * \param $originatingSource string, an url of the data provider.
	*/
	protected 	function create_regObject($group, $key, $originatingSource) {
		$regObj_node = $this->addChild($this->working_node,'registryObject');
		$regObj_node->setAttribute('group',$group);
		$this->addChild($regObj_node,'key',$key);
		$this->addChild($regObj_node,'originatingSource',$originatingSource);
		$this->working_node = $regObj_node;
	}
	
	/** RIF-CS node is the content node of RIF-CS metadata node which starts from regObjects.
	* Each set supportted in RIF-CS has its own content model. The created node will be used as the 
	* root node of this record for following nodes will be created.
	*
	* \param $set_name string, the name of set. For ANDS, they are Activity, Party and Collection
	* \param $set_type string, the type of set. For example, Activity can have project as a type.
	*/
	protected 	function create_rifcs_node($set_name, $set_type) {
		$this->working_node = $this->addChild($this->working_node, $set_name);
		$this->working_node->setAttribute('type', $set_type);
	}

   /**
   * Create a top level name node. 
   * @param $name_type string. Text for the types, can be either primary or abbreviated. Default: primary
   *
   * @return DOMElement $added_name_node.
   *   The newly created node, it will be used for further expansion by adding namePart.
   */
	protected 	function create_name_node($name_type = 'primary') {
		$c = $this->addChild($this->working_node, 'name');
		$c->setAttribute('type', $name_type);
		return $c;
	}
	
   /**
   * Create a namePart of a name node. 
   * @param $name_node
   *   Type: DOMElement. Node of name_node created previously
   *
   * @param $value
   *   Type: string. Text fror this namePart
   *
   * @param $part_type Type: string, used for group:person record. Types can be: titile, given, family
   *
   */
	protected 	function create_namePart($name_node, $value, $part_type = '') {
		$c = $this->addChild($name_node, 'namePart', $value);
		if (!empty($part_type)) {
			$c->setAttribute('type', $part_type);
		}
	}
	
	/** Create related object. One RIF-CS can have more than one related object nodes,
	 * each object is described by one node.
	 * \param $key
	 *	 Type: string. The identifier of the related object.
	 * \param $relation_type
	 *	 Type: string. Type of relationship.
	 * 
	*/
	protected 	function create_relatedObject($key,$relation_type) {
		$c = $this->addChild($this->working_node, 'relatedObject');
		$this->addChild($c,'key',$key);
		$c = $this->addChild($c, 'relation');
		// Mimick ANDS with enpty value to get both tags for relation. Only for better display
		// $c = $this->addChild($c, 'relation','     ');
		$c->setAttribute('type', $relation_type);
	}
	
	/** Create description node. One RIF-CS can have more than one description nodes.
	 * Each description node has only one description.
	 * \param $value Type: string. The content of the description.
	 * \param $des_type Type: string. Type of the description. Types can be brief, full, acessRights and note. Default is 'brief'. 
	*/
	protected 	function create_description_node($value, $des_type='brief') {
		$c = $this->addChild($this->working_node, 'description', $value);
		$c->setAttribute('type', $des_type);
	}

	/** Create local or other type of identifier inside of RIF-CS metadata node
	 * \param $key
	 *	Type string. The indentifier itself.
	 * \param $i_type
	 *	Type string. Type of identifier. Can be abn, uri, local, etc.. Default is local. 
	*/
	protected 	function create_identifier_node($key, $i_type='local') {
		$c = $this->addChild($this->working_node, 'identifier',$key);
		$c->setAttribute('type', $i_type);
	}
	
	/** Location node is a holder node for either address or spatial nodes
	 * \return DOMElement node, for adding address or spatial nodes.
	*/
	protected 	function create_location_node() {
		return $this->addChild($this->working_node, 'location');
	}
	
	/** Address node is a holder node for phiscal or electrical nodes.
	 * \param $location_node Type: DOMElement. Location node created previously.
	 * \return DOMElement
	*/
	protected 	function create_address_node($location_node) {
		return $this->addChild($location_node, 'address');
	}
	
	/** Electrical address node.	Used for email, url, etc
	 * \param $addr_node Type: DOMElement. Previously created address node.
	 * \param $e_node Type: string. The content of the adding node.
	 * \param $e_type Type: string. Default is email.
	*/
	protected 	function create_e_node($addr_node, $e_node, $e_type = 'email') {
		$c = $this->addChild($addr_node, 'electronic');
		$c->setAttribute('type', $e_type);
		$this->addChild($c,'value',$e_node);
	}

	/** Physical node is a holder node for phone or fax nodes.
	 * \param $addr_node Type: DOMelement. Address node created before to which the new phiscial->addressPart will be attached.
	 * \param $number Type: string. Telephone or fax number as a string.
	 * \param $fone_fax Type: string. Either telehoneNumber or faxNumber.
	*/
	protected 	function create_physcial_fone_fax($addr_node, $number,$fone_fax='telephoneNumber') {
		$c = $this->addChild($addr_node, 'physical');
		$c = $this->addChild($c, 'addressPart', $number);
		$c->setAttribute('type', $fone_fax);		
	}

	/** create address node under location node, either streetAddress or postalAddress.
	 * But they are in text (one block) format.
	 * \param $addr_node Type: DOMelement. Address node created before to which the new phiscial->addressPart will be attached.
	 * \param $txt_addr string, full street address in text block format
	 * \param $phys_type string, default is 'streetAddress', can be 'postalAddress'
	*/
	protected 	function create_physcial_addr_txt($addr_node, $txt_addr,$phys_type='streetAddress') {
		$c = $this->addChild($addr_node, 'physical');
		$c->setAttribute('type', $phys_type);
		$c = $this->addChild($c, 'addressPart', $txt_addr);
		$c->setAttribute('type', 'text');		
	}

	/** Create spatial node under a location node.
	 * \param $location_node Type: DOMElement. Location node where spatial node is being added to.
	 * \param $value Type: string. The value of spatial information. Default is local latitude and longitude.
	 * \param $sp_type Type: string. Type of spaitial informaion. Default is kmlPolyCoords.
	*/
	protected 	function create_spatial_node($location_node, $value = '138.6396,-34.97063', $sp_type = 'kmlPolyCoords') {
		$c = $this->addChild($location_node, 'spatial',$value);
		$c->setAttribute('type',$sp_type);
	}

	/** Create temporal coverage node for collection or activity records.
	 * \param $values Type: 2-D array. The values of temporal coverage. It can has maximal two elements: one from 'dateFrom' and another for 'dateTo'.
	 * Either can be ommited according to RIF-CS schema. Each element of $values is an array and has keys: date, type and format.
	 * ['date'] is a string represents date. It has to be in W3CDTF or UTC format.
	 * ['type'] has to be either 'dateFrom' or 'dateTo'. 
	 * ['format'] is optional and its default is 'W3CDTF'. UTC format requires date has to be in UTC: dateTtimeZ.
	 * It throws an exception if the input parameter is not an array.
	 */
	protected 	function create_coverage_tempo($values) {
		// Non array is not acceptable.
		if (!is_array($values)) { throw new Exception('The input of temporal coverage has to be an array of arraies with keys.');}
		$c = $this->addChild($this->working_node,'coverage');
		$t = $this->addChild($c,'temporal');
		foreach($values as $value) $this->create_coverage_tempo_date($t, $value);
	}

	/** Create temporal coverage node for collection or activity records.
	 * \param $t Type: DOMElement. The \<coverage\>\<temporal/\>\</coverage\> node to which \<date\> nodes will be attached to.
	 * \param $value Type: array. The value of temporal coverage. It has maxmimal three elements with keys: type, date and format.
	 * It throws an exception if the input parameter is not an array.
	 * \see create_coverage_tempo
	 */
	private function create_coverage_tempo_date($t, $value) {
		if (!is_array($value)) { throw new Exception('The input of temporal coverage has to be an array with keys.');}
		$d = $this->addChild($t,'date',$value['date']);
		$d->setAttribute('type',$value['type']);
		if (isset($value['format'])) $d->setAttribute('dateFormat',$value['format']);
		else $d->setAttribute('dateFormat','W3CDTF');
	}
	
	/** Create a subject node for a researcher, project, project, etc
	 * \param $value Type: string. A string representing the new namePart.
	 * \param $subject_type Type: string. A string representing the type of subject. The default value is anzsrc-for.
	*/
	protected function create_subject_node($value, $subject_type = 'anzsrc-for') {
		if (empty($value)) return;
		$c = $this->addChild($this->working_node,'subject',$value);
		$c->setAttribute('type',$subject_type);
	}
} // end of class ANDS_RIFCS

