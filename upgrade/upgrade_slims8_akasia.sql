DELETE FROM `setting` WHERE `setting`.`setting_name` = 'barcode_encoding';

--
-- Table structure for table `frei_banned_users`
--
CREATE TABLE IF NOT EXISTS `frei_banned_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `frei_chat`
--
CREATE TABLE IF NOT EXISTS `frei_chat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from` int(11) NOT NULL,
  `from_name` varchar(30) NOT NULL,
  `to` int(11) NOT NULL,
  `to_name` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `sent` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `recd` int(10) unsigned NOT NULL DEFAULT '0',
  `time` double(15,4) NOT NULL,
  `GMT_time` bigint(20) NOT NULL,
  `message_type` int(11) NOT NULL DEFAULT '0',
  `room_id` bigint(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=33 ;

--
-- Table structure for table `frei_config`
--
CREATE TABLE IF NOT EXISTS `frei_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(30) DEFAULT 'NULL',
  `cat` varchar(20) DEFAULT 'NULL',
  `subcat` varchar(20) DEFAULT 'NULL',
  `val` varchar(500) DEFAULT 'NULL',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=67 ;

--
-- Dumping data for table `frei_config`
--
INSERT INTO `frei_config` (`id`, `key`, `cat`, `subcat`, `val`) VALUES
(1, 'PATH', 'NULL', 'NULL', 'freichat/'),
(2, 'show_name', 'NULL', 'NULL', 'user'),
(3, 'displayname', 'NULL', 'NULL', 'username'),
(4, 'chatspeed', 'NULL', 'NULL', '5000'),
(5, 'fxval', 'NULL', 'NULL', 'true'),
(6, 'draggable', 'NULL', 'NULL', 'enable'),
(7, 'conflict', 'NULL', 'NULL', ''),
(8, 'msgSendSpeed', 'NULL', 'NULL', '1000'),
(9, 'show_avatar', 'NULL', 'NULL', 'block'),
(10, 'debug', 'NULL', 'NULL', 'false'),
(11, 'freichat_theme', 'NULL', 'NULL', 'basic'),
(12, 'lang', 'NULL', 'NULL', 'english'),
(13, 'load', 'NULL', 'NULL', 'show'),
(14, 'time', 'NULL', 'NULL', '7'),
(15, 'JSdebug', 'NULL', 'NULL', 'false'),
(16, 'busy_timeOut', 'NULL', 'NULL', '500'),
(17, 'offline_timeOut', 'NULL', 'NULL', '1000'),
(18, 'cache', 'NULL', 'NULL', 'enabled'),
(19, 'GZIP_handler', 'NULL', 'NULL', 'ON'),
(20, 'plugins', 'file_sender', 'show', 'true'),
(21, 'plugins', 'file_sender', 'file_size', '2000'),
(22, 'plugins', 'file_sender', 'expiry', '300'),
(23, 'plugins', 'file_sender', 'valid_exts', 'jpeg,jpg,png,gif,zip'),
(24, 'plugins', 'send_conv', 'show', 'true'),
(25, 'plugins', 'send_conv', 'mailtype', 'smtp'),
(26, 'plugins', 'send_conv', 'smtp_server', 'smtp.gmail.com'),
(27, 'plugins', 'send_conv', 'smtp_port', '465'),
(28, 'plugins', 'send_conv', 'smtp_protocol', 'ssl'),
(29, 'plugins', 'send_conv', 'from_address', 'you@domain.com'),
(30, 'plugins', 'send_conv', 'from_name', 'FreiChat'),
(31, 'playsound', 'NULL', 'NULL', 'true'),
(32, 'ACL', 'filesend', 'user', 'allow'),
(33, 'ACL', 'filesend', 'guest', 'noallow'),
(34, 'ACL', 'chatroom', 'user', 'allow'),
(35, 'ACL', 'chatroom', 'guest', 'allow'),
(36, 'ACL', 'mail', 'user', 'allow'),
(37, 'ACL', 'mail', 'guest', 'allow'),
(38, 'ACL', 'save', 'user', 'allow'),
(39, 'ACL', 'save', 'guest', 'allow'),
(40, 'ACL', 'smiley', 'user', 'allow'),
(41, 'ACL', 'smiley', 'guest', 'allow'),
(42, 'polling', 'NULL', 'NULL', 'disabled'),
(43, 'polling_time', 'NULL', 'NULL', '30'),
(44, 'link_profile', 'NULL', 'NULL', 'disabled'),
(46, 'sef_link_profile', 'NULL', 'NULL', 'disabled'),
(47, 'plugins', 'chatroom', 'location', 'left'),
(48, 'plugins', 'chatroom', 'autoclose', 'true'),
(49, 'content_height', 'NULL', 'NULL', '200px'),
(50, 'chatbox_status', 'NULL', 'NULL', 'false'),
(51, 'BOOT', 'NULL', 'NULL', 'yes'),
(52, 'exit_for_guests', 'NULL', 'NULL', 'yes'),
(53, 'plugins', 'chatroom', 'offset', '50px'),
(54, 'plugins', 'chatroom', 'label_offset', '0.8%'),
(55, 'addedoptions_visibility', 'NULL', 'NULL', 'HIDDEN'),
(56, 'ug_ids', 'NULL', 'NULL', ''),
(57, 'ACL', 'chat', 'user', 'allow'),
(58, 'ACL', 'chat', 'guest', 'allow'),
(59, 'plugins', 'chatroom', 'override_positions', 'yes'),
(60, 'ACL', 'video', 'user', 'allow'),
(61, 'ACL', 'video', 'guest', 'allow'),
(62, 'ACL', 'chatroom_crt', 'user', 'allow'),
(63, 'ACL', 'chatroom_crt', 'guest', 'noallow'),
(64, 'plugins', 'chatroom', 'chatroom_expiry', '3600'),
(65, 'chat_time_shown_always', 'NULL', 'NULL', 'no'),
(66, 'allow_guest_name_change', 'NULL', 'NULL', 'yes');

--
-- Table structure for table `frei_rooms`
--
CREATE TABLE IF NOT EXISTS `frei_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_author` varchar(100) NOT NULL,
  `room_name` varchar(200) NOT NULL,
  `room_type` tinyint(4) NOT NULL,
  `room_password` varchar(100) NOT NULL,
  `room_created` int(11) NOT NULL,
  `room_last_active` int(11) NOT NULL,
  `room_order` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_name` (`room_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `frei_rooms`
--
INSERT INTO `frei_rooms` (`id`, `room_author`, `room_name`, `room_type`, `room_password`, `room_created`, `room_last_active`, `room_order`) VALUES
(1, 'admin', 'Fun Talk', 0, '', 1373557250, 1373557250, 1),
(2, 'admin', 'Crazy chat', 0, '', 1373557260, 1373557260, 5),
(3, 'admin', 'Think out loud', 0, '', 1373557872, 1373557872, 2),
(4, 'admin', 'Talk to me ', 0, '', 1373558017, 1373558017, 3),
(5, 'admin', 'Talk innovative', 0, '', 1373558039, 1373799404, 4);

--
-- Table structure for table `frei_session`
--
CREATE TABLE IF NOT EXISTS `frei_session` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `time` int(100) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `permanent_id` int(100) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `status_mesg` varchar(100) NOT NULL,
  `guest` tinyint(3) NOT NULL,
  `in_room` int(4) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `permanent_id` (`permanent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Table structure for table `frei_smileys`
--
CREATE TABLE IF NOT EXISTS `frei_smileys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `symbol` varchar(10) NOT NULL,
  `image_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

--
-- Dumping data for table `frei_smileys`
--
INSERT INTO `frei_smileys` (`id`, `symbol`, `image_name`) VALUES
(1, ':S', 'worried55231.gif'),
(2, '(wasntme)', 'itwasntme55198.gif'),
(3, 'x(', 'angry55174.gif'),
(4, '(doh)', 'doh55146.gif'),
(5, '|-()', 'yawn55117.gif'),
(6, ']:)', 'evilgrin55088.gif'),
(7, '|(', 'dull55062.gif'),
(8, '|-)', 'sleepy55036.gif'),
(9, '(blush)', 'blush54981.gif'),
(10, ':P', 'tongueout54953.gif'),
(11, '(:|', 'sweat54888.gif'),
(12, ';(', 'crying54854.gif'),
(13, ':)', 'smile54593.gif'),
(14, ':(', 'sad54749.gif'),
(15, ':D', 'bigsmile54781.gif'),
(16, '8)', 'cool54801.gif'),
(17, ':o', 'wink54827.gif'),
(18, '(mm)', 'mmm55255.gif'),
(19, ':x', 'lipssealed55304.gif');

--
-- Table structure for table `frei_video_session`
--
CREATE TABLE IF NOT EXISTS `frei_video_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) DEFAULT NULL COMMENT 'unique room id',
  `from_id` int(11) NOT NULL,
  `msg_type` varchar(10) NOT NULL,
  `msg_label` int(2) NOT NULL,
  `msg_data` varchar(3000) NOT NULL,
  `msg_time` decimal(15,4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `frei_webrtc`
--
CREATE TABLE IF NOT EXISTS `frei_webrtc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frm_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `participants_id` int(11) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Table structure for table `chat_user`
--
CREATE TABLE IF NOT EXISTS `chat_user` (
  `id_chat_user` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `avatar_column_name` varchar(255) NOT NULL,
  `avatar_userid` varchar(255) NOT NULL,
  `avatar_reference_user` varchar(255) NOT NULL,
  `avatar_reference_avatar` varchar(255) NOT NULL,
  `avatar_field_name` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_chat_user`),
  UNIQUE KEY `userid` (`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='SLiMS table for chat support (hendro wicaksono)' AUTO_INCREMENT=1 ;

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