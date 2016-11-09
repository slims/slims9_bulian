<?php
/** \file
 * \brief Definition of RIF-CS handler.
 *
 * It is a plug-in helper function which will be called from where a metadata in rif format is being generated.
 * The name of function defined here cannot be changed.
 * This can also be used as an example for your own metadata strucutre:
 * - create a metetadata node
 * - append contents of the record to the metedata node
 *
 * In this example, every time when a new record is being generated, a new instance of ANDS_TPA is created.
 * As XML output document and the database connection are the same, it is possible to design otherwise.
 *
 * \sa oaidp-config.php
	*/

// This handles RIF-CS records, but can be also used as a sample
// for other formats.
// Just define this function as template to deal your metadata records.

// Create a metadata object and a registryObjects, and its only child registryObject
function create_metadata($outputObj, $cur_record, $identifier, $setspec, $db) {
	// debug_message('In '.__FILE__.' function '.__FUNCTION__.' was called.');

	// debug_var_dump('metadata_node',$metadata_node);
 	$metadata_node = $outputObj->create_metadata($cur_record);
	$obj_node = new ANDS_TPA($outputObj, $metadata_node, $db);
	try {
		$obj_node->create_obj_node($setspec, $identifier);
	} catch (Exception $e) {
			echo 'Caught exception: ',  $e->getMessage(), " when adding $identifier\n";
	}
}

