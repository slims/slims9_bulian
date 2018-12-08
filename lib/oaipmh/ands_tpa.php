<?php
/**
 * \file
 * \brief classes related to generating RIF-CS XML response file for ANDS from repository.
 * It also serves as an exmaple how class ANDS_RIFCS can be used in a particular case.
 *
 */

require_once('ands_rifcs.php');

/**
 * \brief For creating RIF-CS metadata to meet the requirement of ANDS.
 *
 * Class ANDS_RIFCS provides all essential functionalities for creating ANDS RIF-CS records.
 * The protected member functions are the backbone functions which can be used for creating any ANDS RIF-CS records.
 * At the time of design only data source is database and there is only one set of outputs. Therefore there is only one class has been designed. 
 * Ideally, there should be a separated class for creating actual records which reflect data source and data models.
 *
 * Example usage: publish records meet ANDS RIF-CS requirements
 *
 * \code
 * $metadata_node = $outputObj->create_metadata($cur_record);
 * $obj_node = new ANDS_TPA($outputObj, $metadata_node, $db);
 * try {
 * 	$obj_node->create_obj_node($record[$SQL['set']], $identifier);
 * } catch (Exception $e) {
 * 		echo 'Caught exception: ',  $e->getMessage(), " when adding $identifier\n";
 * } 
 * \endcode
 * \see Code in action can be seen in record_rif.php
 */

class ANDS_TPA extends ANDS_RIFCS {
	//! Type: PDO. The database connection of the data source. 
	//! \see __construct. 
	private $db;

	/**
	 * Constructor
	 * The first two parameters are used by its parent class ANDS_RIFCS. The third is its own private property.
	 *
	 * \param $ands_response_doc ANDS_Response_XML. A XML Doc acts as the parent node.
	 * \param $metadata_node DOMElement. The meta node which all subsequent nodes will be added to.
	 * \param $db Type: PDO. The database connection of the data source.
	 */
  function __construct($ands_response_doc, $metadata_node, $db) {
		parent::__construct($ands_response_doc, $metadata_node);
		$this->db = $db;
  }

	
	/**
	 * This is the general entrence of creating actual content. It calls different functions for different type of RIF-CS model.
	 * When anything goes wrong, e.g. found no record, or $set_name is not recognised, an exception will be thrown.
	 * And for this implementation, data are stored in a database therefore a PDO is needed. But the source can be any.
	 *
	 * \param $set_name Type: string. The name of set is going to be created. Can be one of activity, collection or party.
	 * \param $key Type: string. The main identifier used in ANDS system. There can be other identifier.
	 *
	 * \see create_activity, create_collection, create_party
	 */
	function create_obj_node($set_name, $key) {
		$db = $this->db;
		$set_name = strtolower($set_name);
		if (in_array($set_name,prepare_set_names())) {
			try {
				// Get ori_id and which the original table is:
				$query = "select ori_table_name, ori_id from oai_headers where oai_identifier = '".$key."'";
				$res = exec_pdo_query($db, $query);
				$record = $res->fetch(PDO::FETCH_ASSOC);
			} catch (PDOException $e) {
				echo "$key returned no record.\n";
				echo $e->getMessage();
			}

			$processor = 'create_'.substr($set_name,6);
			$this->create_regObject(REG_OBJ_GROUP, $key, MY_URI);
			$this->$processor($record['ori_table_name'],$record['ori_id']);	
			$this->create_identifier_node($key);
			$this->create_identifier_node('table='.$record['ori_table_name'].'+id='.$record['ori_id']);
		} else {
			throw new Exception('Wrong set name was used: '.$set_name);
		}
	}
	
	/** The processor for creating metadata node of Activity. Called from create_obj_node.
	 * \param $table_name Type: string. The table name will be used to retrieve data from.
	 * \param $id_project Type: integer. Internal project id associated to this activity-project.
	 * \see Function create_obj_node.
	 */
	private function create_activity($table_name, $id_project) {
		$db = $this->db;
#		// Get ori_id and which the original table is:
#		$query = "select ori_table_name, ori_id from oai_headers where oai_identifier = '".$key."'";
#		$res = exec_pdo_query($db, $query);
#		$record = $res->fetch(PDO::FETCH_ASSOC);
#		// $id_project will e used later, so save it:
#		$id_project = $record['ori_id'];
		// Get the content using the previously obtained infor:
		$query = sprintf("select inter_no,start_date, end_date,pub_descrip from %s where id_project = %s",$table_name,$id_project);
		
		try {
			$res = exec_pdo_query($db,$query);
			$record = $res->fetch(PDO::FETCH_ASSOC);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		$this->create_rifcs_node('activity','project');
		$c = $this->create_name_node();
		$this->create_namePart($c,'The Plant Accelerator Project '.$record['inter_no']);
// Test codes for rich format.		
#		// \n works
#		$this->create_description_node(sprintf("Line one:%s,\nLine two:%s.\nThird",'a','b'));
		$this->create_description_node(str_replace("\r\n","\n",$record['pub_descrip']));

		$this->create_description_node('The experiment was carried out between '.$record['start_date'].' and '.$record['end_date'],'note');
		$query = sprintf("select idr,stype from list_prj_ids_v2(%d) where stype in ('dataset','person')",$id_project);
		// echo $query;	
		try {
			$res = $db->query($query,PDO::FETCH_ASSOC);
			if ($res==false) {
				throw new Exception($query."\nIt found nothing.\n");
			}
			foreach ($res as $record) {
				switch ($record['stype']) {
					case 'dataset':
						$this->create_relatedObject($record['idr'],'hasOutput');
						break;
					case 'person':
						$this->create_relatedObject($record['idr'],'isManagedBy');
						break;
				} 
			}
			// The Plant Accelerator always participates in Activity
			$this->create_relatedObject('0874ad60-ab4d-11df-aebd-0002a5d5c51b','hasParticipant');
		} catch (PDOException $e) {
			process_pdo_error($query, $e);
		}// end of try-catch block
	} // end of function create_activity($key, $id_project) 
	
	/** The processor for creating metadata node of Collection. Called from create_obj_node.
	 * \param $table_name Type: string. The table name will be used to retrieve data from.
	 * \param $id_collect Type: integer. Internal collection id associated to this collection-dataset.
	 * \see Function create_obj_node.
	 */
	private function create_collection($table_name, $id_collect) {
		$db = $this->db;
		try {
			$query = sprintf("select plant,variety,start_date,end_date,img_freq,anzsrc from %s where id_collect =  %s",$table_name,$id_collect);
			$res = exec_pdo_query($db, $query);
			$dataset = $res->fetch(PDO::FETCH_ASSOC);

			$res = exec_pdo_query($db, $query);
			$record = $res->fetch(PDO::FETCH_ASSOC);
			
			$query = 'select id_rep, inter_no, id_project from tpa_project_ids where id_collect = '.$id_collect;
			$res = exec_pdo_query($db, $query);
			$prj_info = $res->fetch(PDO::FETCH_ASSOC);
			
			$query = 'select email from tpa_person where id_rep = '.$prj_info['id_rep'];
			$res = exec_pdo_query($db, $query);
			$email = $res->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			echo $query.' was failed\n';
			echo $e->getMessage();
		}
		
		$this->create_rifcs_node('collection','dataset');
		// Get the project inter_no as the name of this dataset
		$c = $this->create_name_node();
		$this->create_namePart($c,'Data set of Plant Accelerator Project '.$prj_info['inter_no']);
	
		// locatin node: contact person
		$l_node = $this->create_location_node();
		$a_node = $this->create_address_node($l_node);
		$this->create_e_node($a_node, $email['email']);
		// location node: TPA's physical address
		$l_node = $this->create_location_node();
		$a_node = $this->create_address_node($l_node);
		$this->create_physcial_addr_txt($a_node, 'The Plant Accelerator, Hartley Grove, Urrbrae, SA 5064') ;
		// Temporal coverage of colletion
		$dates = array(array('date'=>$dataset['start_date'],'type'=>'dateFrom'),array('date'=>$dataset['end_date'],'type'=>'dateTo'));
		$this->create_coverage_tempo($dates);
		// subject
		$this->create_subject_node($dataset['aznsrc']);
		// relatedOjbects
		$query = sprintf("select idr,stype from list_prj_ids_v2(%d) where stype in ('project','person')",$prj_info['id_project']);	
		try {
		$res = $db->query($query,PDO::FETCH_ASSOC);
		if ($res==false) {
			throw new Exception($query."\nIt found nothing.\n");
		}
		foreach ($res as $record) {
			switch ($record['stype']) {
				case 'project':
					$this->create_relatedObject($record['idr'],'isOutputOf');
					break;
				case 'person':
					$this->create_relatedObject($record['idr'],'isOwnedBy');
					break;
			} 
		}
		} catch (PDOException $e) {
			process_pdo_error($query, $e);
		}// end of try-catch block
		
		// right of accessing
		$this->create_description_node('For information on rights and access to this dataset, please contact the owner.','accessRights');

		// image data:
		$imgs = ''; $ex_conf = '';
		$dic = array('im_type_rgb'=>'RGB','im_type_nir'=>'NIR','im_type_fir'=>'FIR','im_type_nir_roots'=>'NIR Roots','im_type_fluo'=>'Fluorescence');
		$query = 'select im_type_rgb,im_type_nir,im_type_fir,im_type_nir_roots,im_type_fluo, lines, treatments, replicates, total from ands_collection where id_collect = '. $id_collect;
		$res = $db->query($query,PDO::FETCH_ASSOC);
		if ($res==false) {
			throw new Exception($query."\nIt found nothing.\n");
		}
		$info = $res->fetch();
		foreach ($info as $item => $v) {
			switch ($item) {
				case 'im_type_rgb':
				case 'im_type_nir':
				case 'im_type_fir':
				case 'im_type_nir_roots':
				case 'im_type_fluo':
					if (!empty($v)) { $imgs .= $dic[$item].', '; }
				break;
				default:
					if (!empty($v)) { $ex_conf .= ' '.$item.' = '.$v.', '; }
				break;
			}
		}
		if (empty($imgs)) 		$imgs = "Images data of RGB, FIR, NIR, NIR Roots and Fluorescence cameras., ";
		$imgs = substr($imgs,0,-2);
		if (!empty($ex_conf)) $imgs = $imgs."\n".substr($ex_conf,0,-2);
		$this->create_description_node($imgs);
		// imaging frequency
		$this->create_description_node('Imaging frequency: '.$dataset['img_freq'],'note');
	} // end of function create_collection($key,$id_collect)
	
	/** The processor for creating metadata node of Party. Called from create_obj_node. As party-person is different to party-group, there are two sub-functions are called accordingly.
	 * \param $table_name Type: string. The table name will be used to retrieve data from.
	 * \param $id_party Type: integer. Internal party id associated to this party.
	 * \see Function create_obj_node.
	 */
	private function create_party($table_name, $id_party) {
		$db = $this->db;
		$query = sprintf("SELECT set_type FROM oai_headers WHERE ori_table_name = '%s' AND ori_id = %s",$table_name,$id_party);
		$res = exec_pdo_query($db, $query);
		$party_type = $res->fetch(PDO::FETCH_ASSOC);
		
		if (in_array($party_type['set_type'],array('person','group'))) {
			$this->create_rifcs_node('party',$party_type['set_type']);

			if ($party_type['set_type']=='person') {
				$this->create_person($table_name, $id_party);
			} elseif ($party_type['set_type']=='group') { 
				$this->create_group($table_name, $id_party); }
		} else {
			throw new Exception('Unsupported set_type: '.$party_type['set_type']);		
		}
	} // end of function create_part($key,$id_party)

	/** The processor for creating metadata node of Party. Called from create_obj_node. As party-person is different to party-group, there are two sub-functions are called accordingly.
	 * \param $table_name Type: string. The table name will be used to retrieve data from.
	 * \param $id_party Type: integer. Internal party id associated to this party-person.
	 * \see Function create_party.
	 */
	private 	function create_person($table_name, $id_party) {
		$db = $this->db;
		$query = sprintf("SELECT id_org, title, first_name, family_name, tel, fax, email, www, address, post_code, city,state,country,duty FROM %s WHERE id_rep = %s",$table_name, $id_party);
		$res = exec_pdo_query($db, $query);	
		$info = $res->fetch(PDO::FETCH_ASSOC);
		$c = $this->create_name_node();
		if (!empty($info['title'])) $this->create_namePart($c,$info['title'],'title');
		$this->create_namePart($c,$info['family_name'],'family');
		$this->create_namePart($c,$info['first_name'],'given');
		
		// locatin node: contact person
		$l_node = $this->create_location_node();
		$a_node = $this->create_address_node($l_node);
		$this->create_e_node($a_node, $info['email']);
		if (!empty($info['www'])) $this->create_e_node($a_node, $info['www'],'url');
		$this->create_physcial_fone_fax($a_node, $info['tel'],'telephoneNumber');
		if (!empty($info['fax'])) $this->create_physcial_fone_fax($a_node, $info['fax'],'faxNumber');
		$add_txt = trim($info['address']).', '.$info['city'].' '.$info['state'].' '.$info['post_code'].', '.$info['country'];
		// the strlength of ',   , ' is 6
		if (strlen($add_txt)>6) $this->create_physcial_addr_txt($a_node,$add_txt);
		
		// related objects:
		// their group: id_customer is a foreign key of tpa_organisation
		$query = sprintf("SELECT get_identifier('tpa_organisation',%s)",$info['id_org']);
		$res = exec_pdo_query($db, $query);
		$info = $res->fetch(PDO::FETCH_NUM);
		$this->create_relatedObject($info[0],'isMemberOf');
							
		// their activities
		$query = "SELECT list_persons_objs($id_party,'project')";
		$res = exec_pdo_query($db, $query);
		$info = $res->fetch(PDO::FETCH_NUM);
		foreach ($info as $item) {
			$this->create_relatedObject($item,'isManagerOf');
		}
		// their collections
		$query = "SELECT list_persons_objs($id_party,'dataset')";
		$res = exec_pdo_query($db, $query);
		$info = $res->fetch(PDO::FETCH_NUM);
		foreach ($info as $item) {
			$this->create_relatedObject($item,'isOwnerOf');
		}
	}
	
	/** The processor for creating metadata node of Party. Called from create_obj_node. As party-person is different to party-group, there are two sub-functions are called accordingly.
	 * \param $table_name Type: string. The table name will be used to retrieve data from.
	 * \param $id_party Type: integer. Internal party id associated to this party-group.
	 * \see Function create_party.
	 */
	private function create_group($table_name, $id_party) {
		$db = $this->db;
		// echo 'table: ',$table_name,' party: ',$id_party,"\n";
		$query = sprintf("SELECT customer_name, abn, post_code, address, city, state, country, tel, fax, email, www, description FROM %s WHERE id_org = %s",$table_name, $id_party);
		//echo $query;
		$res = exec_pdo_query($db, $query);
		$info = $res->fetch(PDO::FETCH_ASSOC);
		$c = $this->create_name_node();
		$this->create_namePart($c,$info['customer_name']);
		if (!empty($info['abn'])) $this->create_identifier_node($info['abn'],'abn');

		if (!empty($info['description'])) $this->create_description_node($info['description']);
		
		$l_node = $this->create_location_node();
		$a_node = $this->create_address_node($l_node);
		$this->create_physcial_fone_fax($a_node, $info['tel'],'telephoneNumber');
		$this->create_physcial_fone_fax($a_node, $info['fax'],'faxNumber');
		$add_txt = trim($info['address']).', '.$info['city'].' '.$info['state'].' '.$info['post_code'].', '.$info['country'];
		$this->create_physcial_addr_txt($a_node,$add_txt);
		
		// related objects:
		// their members:
		$query = "SELECT list_pub_members($id_party)";
		$res = exec_pdo_query($db, $query);
		$info = $res->fetch(PDO::FETCH_NUM);
		foreach ($info as $item) {
			$this->create_relatedObject($item,'hasMember');
		}
	}
} // end of class ANDS_TPA

