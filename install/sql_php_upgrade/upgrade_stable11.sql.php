<?php

$sql['truncate'][] = "TRUNCATE TABLE `setting`;";

$sql['insert'][] = "INSERT INTO `setting` (`setting_id`, `setting_name`, `setting_value`) VALUES
(1, 'library_name', 's:7:\"Senayan\";'),
(2, 'library_subname', 's:37:\"Open Source Library Management System\";'),
(3, 'template', 'a:2:{s:5:\"theme\";s:7:\"default\";s:3:\"css\";s:26:\"template/default/style.css\";}'),
(4, 'admin_template', 'a:2:{s:5:\"theme\";s:7:\"default\";s:3:\"css\";s:32:\"admin_template/default/style.css\";}'),
(5, 'default_lang', 's:5:\"en_US\";'),
(6, 'opac_result_num', 's:2:\"10\";'),
(7, 'enable_promote_titles', 'N;'),
(8, 'quick_return', 'b:1;'),
(9, 'allow_loan_date_change', 'b:0;'),
(10, 'loan_limit_override', 'b:0;'),
(11, 'enable_xml_detail', 'b:1;'),
(12, 'enable_xml_result', 'b:1;'),
(13, 'allow_file_download', 'b:1;'),
(14, 'session_timeout', 's:4:\"7200\";');";

$sql['alter'][] = "ALTER TABLE `mst_item_status` ADD `no_loan` SMALLINT( 1 ) NOT NULL DEFAULT '0' AFTER `rules`, ADD INDEX ( no_loan );
ALTER TABLE `mst_item_status` ADD `skip_stock_take` SMALLINT( 1 ) NOT NULL DEFAULT '0' AFTER `no_loan`, ADD INDEX ( skip_stock_take );
UPDATE `mst_item_status` SET `no_loan`=1 WHERE `rules` LIKE '%s:1:\"1\";%';
UPDATE `mst_item_status` SET `skip_stock_take`=1 WHERE `rules` LIKE '%s:1:\"2\";%';";

$sql['alter'][] = "ALTER TABLE `biblio` CHANGE `labels` `labels` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;";

?>