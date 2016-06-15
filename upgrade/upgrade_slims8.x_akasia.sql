--
-- Data setting for visitor limitation
--
INSERT IGNORE INTO `setting` (`setting_name`, `setting_value`) VALUES
('enable_visitor_limitation', 's:1:"0";'),
('time_visitor_limitation', 's:2:"60";');

--
-- Set primary key for 'mst_voc_ctrl'
--
ALTER TABLE `mst_voc_ctrl` ADD PRIMARY KEY(`vocabolary_id`);

-- 
-- Set auto increment for 'vocabolay_id' in 'mst_voc_ctrl'
--
ALTER TABLE `mst_voc_ctrl` CHANGE `vocabolary_id` `vocabolary_id` INT(11) NOT NULL AUTO_INCREMENT;

-- 
-- Set new field for 'uid' in 'biblio'
--
ALTER TABLE  `biblio` ADD  `uid` INT( 11 ) NULL DEFAULT NULL ,
ADD INDEX (  `uid` ) ;

ALTER TABLE  `item` ADD  `uid` INT( 11 ) NULL DEFAULT NULL ,
ADD INDEX (  `uid` ) ;