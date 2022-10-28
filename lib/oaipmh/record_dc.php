<?php
/** \file
 * \brief Definition of Dublin Core handler.
 *
 * It is not working as it does not provide any content to the metadata node. It only included
 * to demonstrate how a new metadata can be supported. For a working
 * example, please see record_rif.php.
 *
 * @author: Ismail Fahmi, ismail.fahmi@gmail.com
 *
 * \sa oaidp-config.php 
	*/

function create_metadata($outputObj, $cur_record, $identifier, $setspec, $db) {
	if (!defined('SLIMS_SERVER_NAME')) define('SLIMS_SERVER_NAME', $_SERVER['SERVER_NAME']);
	if (!defined('SLIMS_BASE_URL')) define('SLIMS_BASE_URL', 'http://'.SLIMS_SERVER_NAME.dirname($_SERVER['SCRIPT_NAME']));

	$metadata_node = $outputObj->create_metadata($cur_record);

    $oai_node = $outputObj->addChild($metadata_node, "oai_dc:dc");
	$oai_node->setAttribute("xmlns:oai_dc","http://www.openarchives.org/OAI/2.0/oai_dc/");
	$oai_node->setAttribute("xmlns:dc","http://purl.org/dc/elements/1.1/");
	$oai_node->setAttribute("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
	$oai_node->setAttribute("xsi:schemaLocation", "http://www.openarchives.org/OAI/2.0/oai_dc/ http://www.openarchives.org/OAI/2.0/oai_dc.xsd");

	$record = get_record($identifier, $db);
	$authors = get_authors($identifier, $db);
	$subjects = get_subjects($identifier, $db);
	$relations = get_relations($identifier, $db);

	$outputObj->addChild($oai_node,'dc:title', xml_safe($record['title']));
	foreach ($authors as $author){
		$outputObj->addChild($oai_node,'dc:creator', xml_safe($author['author_name']));
	}
	foreach ($subjects as $subject){
		$outputObj->addChild($oai_node,'dc:subject', xml_safe($subject['topic']));
	}
	foreach ($relations as $relation){
		$outputObj->addChild($oai_node,'dc:relation', SLIMS_BASE_URL.'/index.php?p=show_detail&amp;id='. $relation['rel_biblio_id']);
	}
	$outputObj->addChild($oai_node,'dc:publisher', xml_safe($record['publisher_name']));
	$outputObj->addChild($oai_node,'dc:date', date_safe($record['publish_year']));
	$outputObj->addChild($oai_node,'dc:language', $record['language_id']);
	$outputObj->addChild($oai_node,'dc:type', $record['gmd_name']);
	$outputObj->addChild($oai_node,'dc:identifier', SLIMS_BASE_URL.'/index.php?p=show_detail&amp;id='. $record['biblio_id']);
	if (!empty($record['isbn_issn'])) 		$outputObj->addChild($oai_node,'dc:identifier_isbn', xml_safe($record['isbn_issn']));
	if (!empty($record['call_number'])) 	$outputObj->addChild($oai_node,'dc:identifier', xml_safe($record['call_number']));	
	if (!empty($record['notes'])) 			$outputObj->addChild($oai_node,'dc:description', xml_safe($record['notes']));
	if (!empty($record['publish_place'])) 	$outputObj->addChild($oai_node,'dc:coverage', xml_safe($record['publish_place']));
	if (!empty($record['image'])) 			$outputObj->addChild($oai_node,'dc:identifier', SLIMS_BASE_URL.'/lib/minigalnano/createthumb.php?filename=images/docs/'. xml_safe($record['image']).'&amp;width=200');
	if (!empty($record['series_title'])) 	$outputObj->addChild($oai_node,'dc:source', xml_safe($record['series_title']));
	if (!empty($record['collation'])) 		$outputObj->addChild($oai_node,'dc:format', xml_safe($record['collation']));
    if (!empty($record['classification'])) 	$outputObj->addChild($oai_node,'dc:subject', xml_safe($record['classification']));
	if (!empty($record['image'])) {
		$outputObj->addChild($oai_node,'image', SLIMS_BASE_URL.'/lib/minigalnano/createthumb.php?filename=images/docs/'. xml_safe($record['image']).'&amp;width=200');
  } else {
		$outputObj->addChild($oai_node,'image', SLIMS_BASE_URL.'/images/default/image.png');
  }
	//print_r($record);
	//print_r($authors);
	//print_r($subjects);
}

function xml_safe($string){
	return preg_replace('/[\x00-\x1f]/','',htmlspecialchars($string??''));
}

function date_safe($string){
	if (preg_match("/(\d{4})/", $string, $matches)){
		return $matches[0];
	} else {
		return xml_safe($string);
	}
}

function get_record ($identifier, $db){
	$query = 'SELECT b.*, l.language_name, p.publisher_name, pl.place_name AS \'publish_place\', gmd.gmd_name, fr.frequency FROM biblio AS b
            LEFT JOIN mst_gmd AS gmd ON b.gmd_id=gmd.gmd_id
            LEFT JOIN mst_language AS l ON b.language_id=l.language_id
            LEFT JOIN mst_publisher AS p ON b.publisher_id=p.publisher_id
            LEFT JOIN mst_place AS pl ON b.publish_place_id=pl.place_id
            LEFT JOIN mst_frequency AS fr ON b.frequency_id=fr.frequency_id
            WHERE biblio_id=' . $identifier;

	$res = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$r = $res->execute();
 	if ($r===false) {
		if (SHOW_QUERY_ERROR) {
			echo __FILE__.','.__LINE__."<br />";
			echo "Query: $query<br />\n";
			print_r($db->errorInfo());
			exit();
		} else {
			return array();
		}		
	} else {
		$record = $res->fetch(PDO::FETCH_ASSOC);
		return $record;
	}
}

function get_authors ($identifier, $db) {
	$query = 'SELECT a.*,ba.level FROM mst_author AS a'
            .' LEFT JOIN biblio_author AS ba ON a.author_id=ba.author_id WHERE ba.biblio_id='.$identifier;

    $res = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$r = $res->execute();
 	if ($r===false) {
		if (SHOW_QUERY_ERROR) {
			echo __FILE__.','.__LINE__."<br />";
			echo "Query: $query<br />\n";
			print_r($db->errorInfo());
			exit();
		} else {
			return array();
		}		
	} else {
		$records = array();
		$hasNext = 1;
		while($hasNext){
			$record = $res->fetch(PDO::FETCH_ASSOC);
			if ($record){
				array_push($records, $record);
			} else {
				$hasNext = 0;
			}
		}

		return $records;
	}
}

function get_subjects($identifier, $db) {
	$query = 'SELECT t.topic, t.topic_type, t.auth_list, bt.level FROM mst_topic AS t
          LEFT JOIN biblio_topic AS bt ON t.topic_id=bt.topic_id WHERE bt.biblio_id='.$identifier.' ORDER BY t.auth_list';

    $res = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$r = $res->execute();
 	if ($r===false) {
		if (SHOW_QUERY_ERROR) {
			echo __FILE__.','.__LINE__."<br />";
			echo "Query: $query<br />\n";
			print_r($db->errorInfo());
			exit();
		} else {
			return array();
		}		
	} else {
		$records = array();
		$hasNext = 1;
		while($hasNext){
			$record = $res->fetch(PDO::FETCH_ASSOC);
			if ($record){
				array_push($records, $record);
			} else {
				$hasNext = 0;
			}
		}

		return $records;
	}
}

function get_digital_files($identifier, $db) {

	// digital files
        $attachment_q = $this->obj_db->query('SELECT att.*, f.* FROM biblio_attachment AS att
            LEFT JOIN files AS f ON att.file_id=f.file_id WHERE att.biblio_id='.$this->detail_id.' AND att.access_type=\'public\' LIMIT 20');
        if ($attachment_q->num_rows > 0) {
          while ($attachment_d = $attachment_q->fetch_assoc()) {
              $_xml_output .= '<dc:relation><![CDATA[';
              // check member type privileges
              if ($attachment_d['access_limit']) { continue; }
              $_xml_output .= preg_replace_callback('/&([a-zA-Z][a-zA-Z0-9]+);/S',
                  'utility::convertXMLentities', htmlspecialchars(trim($attachment_d['file_title'])));
              $_xml_output .= ']]></dc:relation>'."\n";
          }
        }
}

function get_relations ($identifier, $db){
	$query = 'SELECT * FROM biblio_relation WHERE biblio_id=' . $identifier;
	$res = $db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL));
	$r = $res->execute();
 	if ($r===false) {
		if (SHOW_QUERY_ERROR) {
			echo __FILE__.','.__LINE__."<br />";
			echo "Query: $query<br />\n";
			print_r($db->errorInfo());
			exit();
		} else {
			return array();
		}		
	} else {
		$relations = array();
		$hasNext = 1;
		while($hasNext){
			$relation = $res->fetch(PDO::FETCH_ASSOC);
			if ($relation){
				array_push($relations, $relation);
			} else {
				$hasNext = 0;
			}
		}
		return $relations;


	}
}
