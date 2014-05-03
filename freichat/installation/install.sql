DROP TABLE IF EXISTS `frei_chat`;
DROP TABLE IF EXISTS `frei_session`;
DROP TABLE IF EXISTS `frei_config`;
DROP TABLE IF EXISTS `frei_video_session`;
DROP TABLE IF EXISTS `frei_smileys`;
DROP TABLE IF EXISTS `frei_rooms`;


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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;


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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `frei_banned_users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;


CREATE TABLE IF NOT EXISTS `frei_video_session` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` int(11) DEFAULT NULL COMMENT 'unique room id',
  `from_id` int(11) NOT NULL,
  `msg_type` varchar(10) NOT NULL,
  `msg_label` int(2) NOT NULL,
  `msg_data` varchar(3000) NOT NULL,
  `msg_time` decimal(15,4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;

CREATE TABLE IF NOT EXISTS `frei_smileys` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `symbol` varchar(10) NOT NULL,
    `image_name` varchar(50) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;


CREATE TABLE IF NOT EXISTS `frei_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `key` varchar(30) DEFAULT 'NULL',
    `cat` varchar(20) DEFAULT 'NULL',
    `subcat` varchar(20) DEFAULT 'NULL',
    `val` varchar(500) DEFAULT 'NULL',
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=46 ;



INSERT INTO `frei_rooms` (`id`, `room_author`, `room_name`, `room_type`, `room_password`, `room_created`, `room_last_active`, `room_order`) VALUES
        (1, 'admin', 'Fun Talk', 0, '', 1373557250, 1373557250, 1),
        (2, 'admin', 'Crazy chat', 0, '', 1373557260, 1373557260, 5),
        (3, 'admin', 'Think out loud', 0, '', 1373557872, 1373557872, 2),
        (4, 'admin', 'Talk to me ', 0, '', 1373558017, 1373558017, 3),
        (5, 'admin', 'Talk innovative', 0, '', 1373558039, 1373799404, 4);



INSERT IGNORE INTO `frei_smileys` (`id`, `symbol`, `image_name`) VALUES
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




INSERT IGNORE INTO `frei_config` (`id`, `key`, `cat`, `subcat`, `val`) VALUES
        (1, 'PATH', 'NULL', 'NULL', 'freichat/'),
        (2, 'show_name', 'NULL', 'NULL', 'guest'),
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
        (52, 'exit_for_guests', 'NULL', 'NULL', 'no'),
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