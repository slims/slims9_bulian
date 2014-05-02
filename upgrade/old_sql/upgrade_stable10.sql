-- SENAYAN 3.0 stable 10
-- Senayan SQL Database upgrade script

-- bibliography data
ALTER TABLE `biblio` ADD `labels` VARCHAR(200) NULL DEFAULT NULL AFTER `promoted`;
ALTER TABLE `biblio` ADD FULLTEXT (`labels`);
ALTER TABLE `biblio` ADD `frequency_id` INT NULL AFTER `labels`;
ALTER TABLE `biblio` ADD `spec_detail_info` TEXT NULL AFTER `frequency_id`;

-- membership data
ALTER TABLE `member` ADD `birth_date` DATE NULL DEFAULT NULL AFTER `gender`;

-- label master table
CREATE TABLE `mst_label` (
`label_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`label_name` VARCHAR( 20 ) NOT NULL ,
`label_desc` VARCHAR( 50 ) NULL DEFAULT NULL ,
`label_image` VARCHAR( 200 ) NOT NULL ,
`input_date` DATE NOT NULL ,
`last_update` DATE NOT NULL ,
UNIQUE (`label_name`)) ENGINE = MYISAM ;

INSERT INTO `mst_label` (`label_id`, `label_name`, `label_desc`, `label_image`, `input_date`, `last_update`) VALUES
(1, 'label-new', 'New Title', 'label-new.png', '2009-06-30', '2009-06-30'),
(2, 'label-favorite', 'Favorite Title', 'label-favorite.png', '2009-06-30', '2009-06-30'),
(3, 'label-multimedia', 'Multimedia', 'label-multimedia.png', '2009-06-30', '2009-06-30');

-- attachment relation table
CREATE TABLE IF NOT EXISTS `biblio_attachment` (
  `biblio_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `access_type` enum('public','private') collate utf8_unicode_ci NOT NULL,
  KEY `biblio_id` (`biblio_id`),
  KEY `file_id` (`file_id`),
  KEY `biblio_id_2` (`biblio_id`,`file_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- file attachment table
CREATE TABLE IF NOT EXISTS `files` (
  `file_id` int(11) NOT NULL auto_increment,
  `file_title` text collate utf8_unicode_ci NOT NULL,
  `file_name` text collate utf8_unicode_ci NOT NULL,
  `file_url` text collate utf8_unicode_ci,
  `file_dir` text collate utf8_unicode_ci,
  `mime_type` varchar(100) collate utf8_unicode_ci default NULL,
  `file_desc` text collate utf8_unicode_ci,
  `uploader_id` int(11) NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY  (`file_id`),
  FULLTEXT KEY `file_name` (`file_name`),
  FULLTEXT KEY `file_dir` (`file_dir`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- SERIAL MANAGEMENT tables
CREATE TABLE IF NOT EXISTS `mst_frequency` (
`frequency_id` int(11) NOT NULL auto_increment,
`frequency` varchar(25) collate utf8_unicode_ci NOT NULL,
`language_prefix` varchar(5) collate utf8_unicode_ci default NULL,
`time_increment` smallint(6) default NULL,
`time_unit` enum('day','week','month','year') collate utf8_unicode_ci default 'day',
`input_date` date NOT NULL,
`last_update` date NOT NULL,
PRIMARY KEY  (`frequency_id`)
) ENGINE=MyISAM;

-- sample data for serial frequencies
INSERT INTO `mst_frequency` (`frequency`, `language_prefix`, `time_increment`, `time_unit`, `input_date`, `last_update`) VALUES
('Weekly', 'en', 1, 'week', '2009-05-23', '2009-05-23'),
('Bi-weekly', 'en', 2, 'week', '2009-05-23', '2009-05-23'),
('Fourth-Nightly', 'en', 14, 'day', '2009-05-23', '2009-05-23'),
('Monthly', 'en', 1, 'month', '2009-05-23', '2009-05-23'),
('Bi-Monthly', 'en', 2, 'month', '2009-05-23', '2009-05-23'),
('Quarterly', 'en', 3, 'month', '2009-05-23', '2009-05-23'),
('3 Times a Year', 'en', 4, 'month', '2009-05-23', '2009-05-23'),
('Annualy', 'en', 1, 'year', '2009-05-23', '2009-05-23');

CREATE TABLE IF NOT EXISTS `kardex` (
`kardex_id` INT(11) NOT NULL AUTO_INCREMENT ,
`date_expected` DATE NOT NULL ,
`date_received` DATE NOT NULL ,
`seq_number` VARCHAR(25) NULL ,
`notes` TEXT NULL ,
`serial_id` INT(11) NULL ,
`input_date` DATE NOT NULL ,
`last_update` DATE NOT NULL ,
PRIMARY KEY (`kardex_id`) ,
INDEX `fk_serial` (`serial_id` ASC)) ENGINE = MyISAM;

CREATE TABLE IF NOT EXISTS `serial` (
`serial_id` INT(11) NOT NULL AUTO_INCREMENT ,
`date_start` DATE NOT NULL ,
`date_end` DATE DEFAULT NULL ,
`period` VARCHAR(100) NULL ,
`notes` TEXT NULL ,
`biblio_id` INT(11) NULL ,
`gmd_id` INT(11) NULL ,
`input_date` DATE NOT NULL ,
`last_update` DATE NOT NULL ,
PRIMARY KEY (`serial_id`) ,
INDEX `fk_serial_biblio` (`biblio_id` ASC),
INDEX `fk_serial_gmd` (`gmd_id` ASC)
) ENGINE = MyISAM;

INSERT INTO `mst_module` (
`module_id`, `module_name`, `module_path`, `module_desc`
) VALUES (NULL , 'serial_control', 'serial_control', 'Serial publication management');

INSERT INTO `group_access`
VALUES (1, LAST_INSERT_ID(), 1, 1);

-- file attachment conversion
CREATE TABLE `temp_biblio_files` (
`biblio_id` INT(11) NOT NULL AUTO_INCREMENT ,
`file_name` TEXT,
`file_id` INT(11) DEFAULT NULL,
PRIMARY KEY (`biblio_id`),
FULLTEXT `file_name` (`file_name` ASC)
) ENGINE = MyISAM;

INSERT IGNORE INTO `temp_biblio_files` (`biblio_id`, `file_name`)
SELECT `biblio_id`, `file_att` FROM biblio WHERE TRIM(`file_att`)!='' OR file_att IS NOT NULL;

INSERT IGNORE INTO `files` (`file_name`, `file_title`, `file_dir`, `mime_type`, `uploader_id`, `input_date`, `last_update`)
SELECT `file_att`, `file_att`, 'repo1', 'application/octet-stream', 1, NOW(), NOW() FROM biblio WHERE TRIM(`file_att`)!='' OR file_att IS NOT NULL;

-- mimetype updates
UPDATE `files` SET `mime_type`='video/x-flv' WHERE `file_name` LIKE '%.flv';
UPDATE `files` SET `mime_type`='video/mp4' WHERE `file_name` LIKE '%.mp4';
UPDATE `files` SET `mime_type`='audio/mpeg' WHERE `file_name` LIKE '%.mp3';
UPDATE `files` SET `mime_type`='application/msword' WHERE `file_name` LIKE '%.doc';
UPDATE `files` SET `mime_type`='application/ogg' WHERE `file_name` LIKE '%.ogg';
UPDATE `files` SET `mime_type`='application/pdf' WHERE `file_name` LIKE '%.pdf';
UPDATE `files` SET `mime_type`='application/rtf' WHERE `file_name` LIKE '%.rtf';
UPDATE `files` SET `mime_type`='application/vnd.ms-excel' WHERE `file_name` LIKE '%.xls';
UPDATE `files` SET `mime_type`='application/vnd.ms-htmlhelp' WHERE `file_name` LIKE '%.chm';
UPDATE `files` SET `mime_type`='application/vnd.ms-powerpoint' WHERE `file_name` LIKE '%.ppt';
UPDATE `files` SET `mime_type`='application/vnd.oasis.opendocument.chart' WHERE `file_name` LIKE '%.odc';
UPDATE `files` SET `mime_type`='application/vnd.oasis.opendocument.formula' WHERE `file_name` LIKE '%.odf';
UPDATE `files` SET `mime_type`='application/vnd.oasis.opendocument.graphics' WHERE `file_name` LIKE '%.odg';
UPDATE `files` SET `mime_type`='application/vnd.oasis.opendocument.image' WHERE `file_name` LIKE '%.odi';
UPDATE `files` SET `mime_type`='application/vnd.oasis.opendocument.presentation' WHERE `file_name` LIKE '%.odp';
UPDATE `files` SET `mime_type`='application/vnd.oasis.opendocument.spreadsheet' WHERE `file_name` LIKE '%.ods';
UPDATE `files` SET `mime_type`='application/vnd.oasis.opendocument.text' WHERE `file_name` LIKE '%.odt';
UPDATE `files` SET `mime_type`='application/x-shockwave-flash' WHERE `file_name` LIKE '%.swf';
UPDATE `files` SET `mime_type`='application/zip' WHERE `file_name` LIKE '%.zip';
UPDATE `files` SET `mime_type`='image/jpeg' WHERE `file_name` LIKE '%.jpg' OR `file_name` LIKE '%.jpeg';
UPDATE `files` SET `mime_type`='image/png' WHERE `file_name` LIKE '%.png';
UPDATE `files` SET `mime_type`='image/gif' WHERE `file_name` LIKE '%.gif';

UPDATE `temp_biblio_files` AS tbf LEFT JOIN `files` AS f ON tbf.file_name=f.file_name
SET tbf.file_id=f.file_id;

INSERT IGNORE INTO `biblio_attachment`
SELECT `biblio_id`, `file_id`, 'public' FROM `temp_biblio_files`;

DROP TABLE `temp_biblio_files`;

ALTER TABLE `mst_author` ADD `auth_list` VARCHAR( 20 ) NULL DEFAULT NULL AFTER `authority_type`;
ALTER TABLE `mst_topic` ADD `topic_type` ENUM( 't', 'g', 'n', 'tm', 'gr', 'oc' ) NOT NULL AFTER `topic`;
ALTER TABLE `mst_topic` ADD `auth_list` VARCHAR( 20 ) NULL DEFAULT NULL AFTER `topic_type`;
