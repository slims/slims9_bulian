IF object_id('frei_session', 'U') is not null
	DROP TABLE "frei_session";
	
IF object_id('frei_config', 'U') is not null
	DROP TABLE "frei_config";
	
IF object_id('frei_chat', 'U') is not null
	DROP TABLE "frei_chat";

IF object_id('frei_smileys', 'U') is not null
	DROP TABLE "frei_smileys";

IF object_id('frei_rooms', 'U') is not null
	DROP TABLE "frei_smileys";

IF object_id('frei_banned_users', 'U') is null 
BEGIN
	CREATE TABLE [dbo].[frei_banned_users](
		[id] [int] IDENTITY(840,1) NOT NULL,
		[user_id] [int] NOT NULL,
	 CONSTRAINT [PK_frei_banned_users_id] PRIMARY KEY CLUSTERED 
	(
		[id] ASC
	)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
	 CONSTRAINT [frei_banned_users$user_id] UNIQUE NONCLUSTERED 
	(
		[user_id] ASC
	)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
	) ON [PRIMARY]
END;

IF object_id('frei_chat', 'U') is null
BEGIN
	CREATE TABLE [dbo].[frei_chat](
		[id] [bigint] IDENTITY(1,1) NOT NULL,
		[from] [int] NOT NULL,
		[from_name] [nvarchar](30) NOT NULL,
		[to] [int] NOT NULL,
		[to_name] [nvarchar](30) NOT NULL,
		[message] [nvarchar](max) NOT NULL,
		[sent] [datetime2](0) NOT NULL,
		[recd] [bigint] NOT NULL,
		[time] [numeric](15, 4) NOT NULL,
		[GMT_time] [bigint] NOT NULL,
		[message_type] [int] NOT NULL,
		[room_id] [int] NOT NULL,
	 CONSTRAINT [PK_frei_chat_id] PRIMARY KEY CLUSTERED 
	(
		[id] ASC
	)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
	) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
END;

IF object_id('frei_config', 'U') is null
BEGIN
	CREATE TABLE [dbo].[frei_config](
		[id] [int] IDENTITY(60,1) NOT NULL,
		[key] [nvarchar](30) NULL,
		[cat] [nvarchar](20) NULL,
		[subcat] [nvarchar](20) NULL,
		[val] [nvarchar](500) NULL,
	 CONSTRAINT [PK_frei_config_id] PRIMARY KEY CLUSTERED 
	(
		[id] ASC
	)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
	) ON [PRIMARY]
	INSERT INTO "frei_config" ( "key", "cat", "subcat", "val") VALUES
			( 'PATH', 'NULL', 'NULL', 'freichat/'),
			( 'show_name', 'NULL', 'NULL', 'guest'),
			( 'displayname', 'NULL', 'NULL', 'username'),
			( 'chatspeed', 'NULL', 'NULL', '5000'),
			( 'fxval', 'NULL', 'NULL', 'true'),
			( 'draggable', 'NULL', 'NULL', 'enable'),
			( 'conflict', 'NULL', 'NULL', ''),
			( 'msgSendSpeed', 'NULL', 'NULL', '1000'),
			( 'show_avatar', 'NULL', 'NULL', 'block'),
			( 'debug', 'NULL', 'NULL', 'false'),
			( 'freichat_theme', 'NULL', 'NULL', 'basic'),
			( 'lang', 'NULL', 'NULL', 'english'),
			( 'load', 'NULL', 'NULL', 'show'),
			( 'time', 'NULL', 'NULL', '7'),
			( 'JSdebug', 'NULL', 'NULL', 'false'),
			( 'busy_timeOut', 'NULL', 'NULL', '500'),
			( 'offline_timeOut', 'NULL', 'NULL', '1000'),
			( 'cache', 'NULL', 'NULL', 'enabled'),
			( 'GZIP_handler', 'NULL', 'NULL', 'ON'),
			( 'plugins', 'file_sender', 'show', 'true'),
			( 'plugins', 'file_sender', 'file_size', '2000'),
			( 'plugins', 'file_sender', 'expiry', '300'),
			( 'plugins', 'file_sender', 'valid_exts', 'jpeg,jpg,png,gif,zip'),
			( 'plugins', 'send_conv', 'show', 'true'),
			( 'plugins', 'send_conv', 'mailtype', 'smtp'),
			( 'plugins', 'send_conv', 'smtp_server', 'smtp.gmail.com'),
			( 'plugins', 'send_conv', 'smtp_port', '465'),
			( 'plugins', 'send_conv', 'smtp_protocol', 'ssl'),
			( 'plugins', 'send_conv', 'from_address', 'you@domain.com'),
			( 'plugins', 'send_conv', 'from_name', 'FreiChat'),
			( 'playsound', 'NULL', 'NULL', 'true'),
			( 'ACL', 'filesend', 'user', 'allow'),
			( 'ACL', 'filesend', 'guest', 'noallow'),
			( 'ACL', 'chatroom', 'user', 'allow'),
			( 'ACL', 'chatroom', 'guest', 'allow'),
			( 'ACL', 'mail', 'user', 'allow'),
			( 'ACL', 'mail', 'guest', 'allow'),
			( 'ACL', 'save', 'user', 'allow'),
			( 'ACL', 'save', 'guest', 'allow'),
			( 'ACL', 'smiley', 'user', 'allow'),
			( 'ACL', 'smiley', 'guest', 'allow'),
			( 'polling', 'NULL', 'NULL', 'disabled'),
			( 'polling_time', 'NULL', 'NULL', '30'),
			( 'link_profile', 'NULL', 'NULL', 'disabled'),
			( 'sef_link_profile', 'NULL', 'NULL', 'disabled'),
			( 'plugins', 'chatroom', 'location', 'left'),
			( 'plugins', 'chatroom', 'autoclose', 'true'),
			( 'content_height', 'NULL', 'NULL', '200px'),
			( 'chatbox_status', 'NULL', 'NULL', 'false'),
			( 'BOOT', 'NULL', 'NULL', 'yes'),
			( 'exit_for_guests', 'NULL', 'NULL', 'no'),
			( 'plugins', 'chatroom', 'offset', '50px'),
			( 'plugins', 'chatroom', 'label_offset', '0.8%'),
			( 'addedoptions_visibility', 'NULL', 'NULL', 'HIDDEN'),
			( 'ug_ids', 'NULL', 'NULL', ''),
			( 'ACL', 'chat', 'user', 'allow'),
			( 'ACL', 'chat', 'guest', 'allow'),
                        ( 'plugins', 'chatroom', 'override_positions', 'yes'),
                        ( 'ACL', 'video', 'user', 'allow'),
                        ( 'ACL', 'video', 'guest', 'allow'),
                        ( 'ACL', 'chatroom_crt', 'user', 'allow'),
                        ( 'ACL', 'chatroom_crt', 'guest', 'noallow'),
                        ( 'plugins', 'chatroom', 'chatroom_expiry', '3600'),
                        ( 'chat_time_shown_always', 'NULL', 'NULL', 'no'),
                        ( 'allow_guest_name_change', 'NULL', 'NULL', 'yes');
END;

IF object_id('frei_rooms', 'U') is null
BEGIN
        CREATE TABLE [dbo].[frei_rooms](
                [id] [int] IDENTITY(6,1) NOT NULL,
                [room_author] [nvarchar](100) NOT NULL,
                [room_name] [nvarchar](200) NOT NULL,
                [room_type] [smallint] NOT NULL,
                [room_password] [nvarchar](100) NOT NULL,
                [room_created] [int] NOT NULL,
                [room_last_active] [int] NOT NULL,
                [room_order] [smallint] NOT NULL,
         CONSTRAINT [PK_frei_rooms_id] PRIMARY KEY CLUSTERED 
        (
                [id] ASC
        )WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
         CONSTRAINT [frei_rooms$room_name] UNIQUE NONCLUSTERED 
        (
                [room_name] ASC
        )WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
        ) ON [PRIMARY]
        INSERT INTO `frei_rooms` (`id`, `room_author`, `room_name`, `room_type`, `room_password`, `room_created`, `room_last_active`, `room_order`) VALUES
                ( 'admin', 'Fun Talk', 0, '', 1373557250, 1373557250, 1),
                ( 'admin', 'Crazy chat', 0, '', 1373557260, 1373557260, 5),
                ( 'admin', 'Think out loud', 0, '', 1373557872, 1373557872, 2),
                ( 'admin', 'Talk to me ', 0, '', 1373558017, 1373558017, 3),
                ( 'admin', 'Talk innovative', 0, '', 1373558039, 1373799404, 4)
END;

IF object_id('frei_session', 'U') is null
BEGIN
	CREATE TABLE [dbo].[frei_session](
		[id] [int] IDENTITY(1,1) NOT NULL,
		[username] [nvarchar](255) NULL,
		[time] [int] NOT NULL,
		[session_id] [nvarchar](100) NOT NULL,
		[permanent_id] [int] NOT NULL,
		[status] [smallint] NOT NULL,
		[status_mesg] [nvarchar](100) NOT NULL,
		[guest] [smallint] NOT NULL,
		[in_room] [int] NOT NULL,
	 CONSTRAINT [PK_frei_session_id] PRIMARY KEY CLUSTERED 
	(
		[id] ASC
	)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY],
	 CONSTRAINT [frei_session$permanent_id] UNIQUE NONCLUSTERED 
	(
		[permanent_id] ASC
	)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
	) ON [PRIMARY]
END;

IF object_id('frei_smileys', 'U') is null
BEGIN
	CREATE TABLE [dbo].[frei_smileys](
		[id] [int] IDENTITY(20,1) NOT NULL,
		[symbol] [nvarchar](10) NOT NULL,
		[image_name] [nvarchar](50) NOT NULL,
	 CONSTRAINT [PK_frei_smileys_id] PRIMARY KEY CLUSTERED 
	(
		[id] ASC
	)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
	) ON [PRIMARY]

        INSERT INTO `frei_smileys` ( `symbol`, `image_name`) VALUES
                        ( ':S', 'worried55231.gif'),
                        ( '(wasntme)', 'itwasntme55198.gif'),
                        ( 'x(', 'angry55174.gif'),
                        ( '(doh)', 'doh55146.gif'),
                        ( '|-()', 'yawn55117.gif'),
                        ( ']:)', 'evilgrin55088.gif'),
                        ( '|(', 'dull55062.gif'),
                        ( '|-)', 'sleepy55036.gif'),
                        ( '(blush)', 'blush54981.gif'),
                        ( ':P', 'tongueout54953.gif'),
                        ( '(:|', 'sweat54888.gif'),
                        ( ';(', 'crying54854.gif'),
                        ( ':)', 'smile54593.gif'),
                        ( ':(', 'sad54749.gif'),
                        ( ':D', 'bigsmile54781.gif'),
                        ( '8)', 'cool54801.gif'),
                        ( ':o', 'wink54827.gif'),
                        ( '(mm)', 'mmm55255.gif'),
                        ( ':x', 'lipssealed55304.gif')

END;

IF object_id('frei_video_session', 'U') is null
BEGIN
	CREATE TABLE [dbo].[frei_video_session](
		[id] [int] IDENTITY(1,1) NOT NULL,
		[rid] [int] NOT NULL,
		[from_id] [int] NOT NULL,
		[msg_type] [nvarchar](10) NOT NULL,
		[msg_label] [int] NOT NULL,
		[msg_data] [nvarchar](3000) NOT NULL,
		[msg_time] [decimal](15, 4) NOT NULL
	 CONSTRAINT [PK_frei_video_session_id] PRIMARY KEY CLUSTERED 
	(
		[id] ASC
	)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
	) ON [PRIMARY]
END;


ALTER TABLE [dbo].[frei_chat] ADD  CONSTRAINT [DF__frei_chat__sent__3D5E1FD2]  DEFAULT (getdate()) FOR [sent];

ALTER TABLE [dbo].[frei_chat] ADD  CONSTRAINT [DF__frei_chat__recd__3E52440B]  DEFAULT ((0)) FOR [recd];

ALTER TABLE [dbo].[frei_chat] ADD  CONSTRAINT [DF__frei_chat__messa__3F466844]  DEFAULT ((0)) FOR [message_type];

ALTER TABLE [dbo].[frei_config] ADD  DEFAULT (N'NULL') FOR [key];

ALTER TABLE [dbo].[frei_config] ADD  DEFAULT (N'NULL') FOR [cat];

ALTER TABLE [dbo].[frei_config] ADD  DEFAULT (N'NULL') FOR [subcat];

ALTER TABLE [dbo].[frei_config] ADD  DEFAULT (N'NULL') FOR [val];

ALTER TABLE [dbo].[frei_session] ADD  DEFAULT (NULL) FOR [username];

ALTER TABLE [dbo].[frei_session] ADD  DEFAULT ((-1)) FOR [in_room];