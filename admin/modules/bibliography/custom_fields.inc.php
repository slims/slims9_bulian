<?php
/**
 * Bibliographic custom fields
 *
 * Copyright (C) 2010  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// be sure that this file not accessed directly
if (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

/**
 * Here you can add custom field to SLiMS Bibliographic database
 * The field you define here must also exists in 'biblio_custom' table in database
 * field 'type' can be on of these: 'text', 'longtext', 'numeric', 'dropdown', 'checklist', 'date' or 'choice'
 *
 */

/*
$biblio_custom_fields = array(
	'customfield1' => array(
		'dbfield' => 'customfield1', // name of field in 'biblio_custom' table in database, make sure you already define it!
		'label' => __('Custom Field 1'), // label of field in form
		'type' => 'text', // type of field
		'default' => '', // default value of field
		'max' => '50', // maximum character to enter in 'text' field type
		'data' => false, // an array of data for 'dropdown', 'checklist' or 'choice'
		'indexed' => true, // NOT APPLICABLE YET, FOR FUTURE RELEASE USE
		'width' => 50), // width of field in form for 'text' field type, maximum is 100

	'customfield2' => array(
		'dbfield' => 'customfield2',
		'label' => __('Custom Field 2'),
		'type' => 'dropdown',
		'default' => 'value2',
		'data' => array(
			array('value1', 'Value 1'),
			array('value2', 'Value 2')
			),
		'indexed' => true),

	'customfield3' => array(
		'dbfield' => 'customfield3',
		'label' => __('Custom Field 3'),
		'type' => 'checklist',
		'default' => array('value2', 'value3'),
		'data' => array(
			array('value1', 'Value 1'),
			array('value2', 'Value 2'),
			array('value3', 'Value 3')
			),
		'indexed' => true),

	'customfield4' => array(
		'dbfield' => 'customfield4',
		'label' => __('Custom Field 4'),
		'type' => 'choice',
		'default' => 'value2',
		'data' => array(
			array('value1', 'Value 1'),
			array('value2', 'Value 2')
			),
		'indexed' => true),

	'customfield5' => array(
		'dbfield' => 'customfield5',
		'label' => __('Custom Field 5'),
		'default' => date('Y-m-d'),
		'type' => 'date')
);
*/
