<?php
/*
--
-- Data setting for visitor limitation
--
*/
$sql['insert'][] = "INSERT IGNORE INTO `setting` (`setting_name`, `setting_value`) VALUES
('enable_visitor_limitation', 's:1:\"0\";'),
('time_visitor_limitation', 's:2:\"60\";');";

/*
-- 
-- Set auto increment for 'vocabolay_id' in 'mst_voc_ctrl'
--
*/
$sql['alter'][] = "ALTER TABLE `mst_voc_ctrl` CHANGE `vocabolary_id` `vocabolary_id` INT(11) NOT NULL AUTO_INCREMENT;";
