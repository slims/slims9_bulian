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

--
-- Table structure for table `mst_servers`
--

CREATE TABLE `mst_servers` (
  `server_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `uri` text COLLATE utf8_unicode_ci NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for table `mst_servers`
--
ALTER TABLE `mst_servers`
  ADD PRIMARY KEY (`server_id`);

--
-- AUTO_INCREMENT for table `mst_servers`
--
ALTER TABLE `mst_servers`
  MODIFY `server_id` int(11) NOT NULL AUTO_INCREMENT;