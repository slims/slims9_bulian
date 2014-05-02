-- SENAYAN 3.0 stable 14
-- Senayan SQL Database upgrade script

CREATE TABLE IF NOT EXISTS `visitor_count` (
  `visitor_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `member_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `institution` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `checkin_date` datetime NOT NULL,
  PRIMARY KEY (`visitor_id`),
  KEY `member_id` (`member_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `biblio_custom` (
`biblio_id` INT NOT NULL ,
PRIMARY KEY ( `biblio_id` )
) ENGINE = MYISAM COMMENT = 'one to one relation with real biblio table';