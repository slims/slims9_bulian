<?php 
/**
 * \file
 * \brief Response to Verb ListSets
 *
 * Lists what sets are available to records in the system. 
 */

// Here the size of sets is small, no resumptionToken is taken care.
if (is_array($SETS)) {
	$outputObj = new ANDS_Response_XML($args);
	foreach($SETS as $set) {
		$setNode = $outputObj->add2_verbNode("set");
		foreach($set as $key => $val) {
			if($key=='setDescription') {
				$desNode = $outputObj->addChild($setNode,$key);
				$des = $outputObj->doc->createDocumentFragment();
				$des->appendXML($val);
				$desNode->appendChild($des);
			} else {
				$outputObj->addChild($setNode,$key,$val);
			}
		}
	}
}	else {
	$errors[] = oai_error('noSetHierarchy'); 
	oai_exit();
}

?>
