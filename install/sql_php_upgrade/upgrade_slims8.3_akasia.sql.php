<?php

/*
-- 
-- Set new field for 'uid' in 'biblio'
--
*/
$sql['alter'][] = "ALTER TABLE  `biblio` ADD  `uid` INT( 11 ) NULL DEFAULT NULL ,
ADD INDEX (  `uid` ) ;";

$sql['alter'][] = "ALTER TABLE  `item` ADD  `uid` INT( 11 ) NULL DEFAULT NULL ,
ADD INDEX (  `uid` ) ;";


/*
--
-- Table structure for table `mst_servers`
--
*/
$sql['create'][] = "CREATE TABLE `mst_servers` (
  `server_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `uri` text COLLATE utf8_unicode_ci NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

/*
--
-- Indexes for table `mst_servers`
--
*/
$sql['alter'][] = "ALTER TABLE `mst_servers` ADD PRIMARY KEY (`server_id`);";

/*
--
-- AUTO_INCREMENT for table `mst_servers`
--
*/
$sql['alter'][] = "ALTER TABLE `mst_servers` MODIFY `server_id` int(11) NOT NULL AUTO_INCREMENT;";

/*
-- 
-- Set new field for 'scope' in 'mst_voc_ctrl'
--
*/
$sql['alter'][] = "ALTER TABLE `mst_voc_ctrl` ADD `scope` TEXT NULL DEFAULT NULL; ";