<?php

$sql['alter'][] = "ALTER TABLE  `mst_topic` ADD  `classification` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT  'Classification Code' AFTER  `auth_list` ;";
$sql['alter'][] = "ALTER TABLE `biblio` ADD `sor` VARCHAR( 200 ) COLLATE utf8_unicode_ci NULL AFTER `title` ;";
$sql['insert'][] = "INSERT INTO `setting` (`setting_id`, `setting_name`, `setting_value`) VALUES (NULL , 'ignore_holidays_fine_calc', 'b:0;');";

?>