DELETE FROM `setting` WHERE `setting`.`setting_name` = 'barcode_encoding';

ALTER TABLE `biblio` ADD `content_type_id` INT NULL DEFAULT NULL AFTER `spec_detail_info`,
  ADD `media_type_id` INT NULL DEFAULT NULL AFTER `content_type_id`,
  ADD `carrier_type_id` INT NULL DEFAULT NULL AFTER `media_type_id`,
  ADD INDEX (`content_type_id`, `media_type_id`, `carrier_type_id`) ;
  
ALTER TABLE `search_biblio` ADD `content_type` VARCHAR(100) NULL DEFAULT NULL AFTER `image`, ADD `media_type` VARCHAR(100) NULL DEFAULT NULL AFTER `content_type`,
  ADD `carrier_type` VARCHAR(100) NULL DEFAULT NULL AFTER `media_type`, ADD INDEX (`content_type`, `media_type`, `carrier_type`);

--
-- Table structure for table `mst_carrier_type`
--

CREATE TABLE IF NOT EXISTS `mst_carrier_type` (
`id` int(11) NOT NULL,
  `carrier_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `code2` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `mst_carrier_type`
--

INSERT INTO `mst_carrier_type` (`id`, `carrier_type`, `code`, `code2`, `input_date`, `last_update`) VALUES
(1, 'audio cartridge ', ' sg ', 'g', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'audio cylinder ', ' se ', 'e', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'audio disc ', ' sd ', 'd', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'sound track reel', ' si', 'i', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'audio roll ', ' sq ', 'q', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'audiocassette ', ' ss ', 's', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'audiotape reel ', ' st ', 't', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'other (audio)', ' sz ', 'z', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'computer card ', ' ck ', 'k', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 'computer chip cartridge ', ' cb ', 'b', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 'computer disc ', ' cd ', 'd', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 'computer disc cartridge ', ' ce ', 'e', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(13, 'computer tape cartridge ', ' ca ', 'a', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(14, 'computer tape cassette ', ' cf ', 'f', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(15, 'computer tape reel ', ' ch ', 'h', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(16, 'online resource ', ' cr ', 'r', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(17, 'other (computer)', ' cz ', 'z', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(18, 'aperture card ', ' ha ', 'a', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(19, 'microfiche ', ' he ', 'e', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(20, 'microfiche cassette ', ' hf ', 'f', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(21, 'microfilm cartridge ', ' hb ', 'b', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(22, 'microfilm cassette ', ' hc ', 'c', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(23, 'microfilm reel ', ' hd ', 'd', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(24, 'microfilm roll', ' hj', 'j', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(25, 'microfilm slip ', ' hh ', 'h', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(26, 'microopaque ', ' hg ', 'g', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(27, 'other (microform)', ' hz ', 'z', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(28, 'microscope slide ', ' pp ', 'p', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(29, 'other (microscope)', ' pz ', 'z', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(30, 'film cartridge ', ' mc ', 'c', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(31, 'film cassette ', ' mf ', 'f', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(32, 'film reel ', ' mr ', 'r', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(33, 'film roll', ' mo', 'o', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(34, 'filmslip ', ' gd ', 'd', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(35, 'filmstrip ', ' gf ', 'f', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(36, 'filmstrip cartridge ', ' gc ', 'c', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(37, 'overhead transparency ', ' gt ', 't', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(38, 'slide ', ' gs ', 's', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(39, 'other (projected image)', ' mz ', 'z', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(40, 'stereograph card ', ' eh ', 'h', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(41, 'stereograph disc ', ' es ', 's', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(42, 'other (stereographic)', ' ez ', 'z', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(43, 'card ', ' no', 'o', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(44, 'flipchart ', ' nn', 'n', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(45, 'roll ', ' na ', 'a', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(46, 'sheet ', ' nb ', 'b', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(47, 'volume ', ' nc ', 'c', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(48, 'object', ' nr', 'r', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(49, 'other (unmediated)', ' nz ', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(50, 'unspecified ', ' zu ', 'u', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `mst_content_type`
--

CREATE TABLE IF NOT EXISTS `mst_content_type` (
`id` int(11) NOT NULL,
  `content_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `code2` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `mst_content_type`
--

INSERT INTO `mst_content_type` (`id`, `content_type`, `code`, `code2`, `input_date`, `last_update`) VALUES
(1, 'cartographic dataset ', 'crd ', 'e', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'cartographic image ', 'cri ', 'e', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'cartographic moving image ', 'crm ', 'e', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'cartographic tactile image ', 'crt ', 'e', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'cartographic tactile three-dimensional form ', 'crn ', 'e', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'cartographic three-dimensional form ', 'crf ', 'e', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'computer dataset ', 'cod ', 'm', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'computer program ', 'cop ', 'm', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'notated movement ', 'ntv ', 'a', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 'notated music ', 'ntm ', 'c', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(11, 'performed music ', 'prm ', 'j', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(12, 'sounds ', 'snd ', 'i', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(13, 'spoken word ', 'spw ', 'i', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(14, 'still image ', 'sti ', 'k', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(15, 'tactile image ', 'tci ', 'k', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(16, 'tactile notated music ', 'tcm ', 'c', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(17, 'tactile notated movement ', 'tcn ', 'a', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(18, 'tactile text ', 'tct ', 'a', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(19, 'tactile three-dimensional form ', 'tcf ', 'r', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(20, 'text ', 'txt ', 'a', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(21, 'three-dimensional form ', 'tdf ', 'r', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(22, 'three-dimensional moving image ', 'tdm ', 'g', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(23, 'two-dimensional moving image ', 'tdi ', 'g', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(24, 'other ', 'xxx ', 'o', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(25, 'unspecified ', 'zzz ', ' ', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `mst_media_type`
--

CREATE TABLE IF NOT EXISTS `mst_media_type` (
`id` int(11) NOT NULL,
  `media_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `code2` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `mst_media_type`
--

INSERT INTO `mst_media_type` (`id`, `media_type`, `code`, `code2`, `input_date`, `last_update`) VALUES
(1, 'audio ', 's ', 's', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'computer ', 'c ', 'c', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'microform ', 'h ', 'h', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'microscopic ', 'p ', ' ', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'projected ', 'g ', 'g', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(6, 'stereographic ', 'e ', ' ', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(7, 'unmediated ', 'n ', 't', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(8, 'video ', 'v ', 'v', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(9, 'other ', 'x ', 'z', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(10, 'unspecified ', 'z ', 'z', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

ALTER TABLE `mst_carrier_type`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `media_type` (`carrier_type`), ADD KEY `code` (`code`);

ALTER TABLE `mst_content_type`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `content_type` (`content_type`), ADD KEY `code` (`code`);

ALTER TABLE `mst_media_type`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `media_type` (`media_type`), ADD KEY `code` (`code`);

ALTER TABLE `mst_carrier_type`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=51;

ALTER TABLE `mst_content_type`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=26;

ALTER TABLE `mst_media_type`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;