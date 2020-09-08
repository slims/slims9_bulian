-- SENAYAN Library Automation
-- Version 3.0 stable 9
-- Sample data for senayan library automation

--
-- Dumping data for table `biblio`
--

INSERT IGNORE INTO `biblio` (`biblio_id`, `gmd_id`, `title`, `edition`, `isbn_issn`, `publisher_id`, `publish_year`, `collation`, `series_title`, `call_number`, `language_id`, `source`, `publish_place_id`, `classification`, `notes`, `image`, `file_att`, `opac_hide`, `promoted`, `input_date`, `last_update`) VALUES
(1, 1, 'PHP 5 for dummies', NULL, '0764541668', 1, 2004, 'xiv, 392 p. : ill. ; 24 cm.', 'For dummies', '005.13/3-22 Jan p', 'en', NULL, 1, '005.13/3 22', NULL, 'php5_dummies.jpg', NULL, 0, 0, '2007-11-29 15:36:50', '2007-11-29 16:26:59'),
(2, 1, 'Linux In a Nutshell', 'Fifth Edition', '9780596009304', 2, 2005, 'xiv, 925 p. : ill. ; 23 cm.', 'In a Nutshell', '005.4/32-22 Ell l', 'en', NULL, 2, '005.4/32 22', NULL, 'linux_in_a_nutshell.jpg', NULL, 0, 0, '2007-11-29 15:53:35', '2007-11-29 16:26:10'),
(3, 1, 'The Definitive Guide to MySQL 5', NULL, '9781590595350', 3, 2005, '784p.', 'Definitive Guide Series', '005.75/85-22 Kof d', 'en', NULL, NULL, '005.75/85 22', NULL, 'mysql_def_guide.jpg', NULL, 0, 0, '2007-11-29 16:01:08', '2007-11-29 16:26:33'),
(4, 1, 'Cathedral and the Bazaar: Musings on Linux and Open Source by an Accidental Revolutionary', NULL, '0-596-00108-8', 2, 2001, '208p.', NULL, '005.4/3222 Ray c', 'en', NULL, 2, '005.4/32 22', 'The Cathedral & the Bazaar is a must for anyone who cares about the future of the computer industry or the dynamics of the information economy. This revised and expanded paperback edition includes new material on open source developments in 1999 and 2000. Raymond''s clear and effective writing style accurately describing the benefits of open source software has been key to its success. (Source: http://safari.oreilly.com/0596001088)', 'cathedral_bazaar.jpg', 'cathedral-bazaar.pdf', 0, 0, '2007-11-29 16:14:44', '2007-11-29 16:25:43'),
(5, 1, 'Producing open source software : how to run a successful free software project', '1st ed.', '9780596007591', 2, 2005, 'xx, 279 p. ; 24 cm.', NULL, '005.1-22 Fog p', 'en', NULL, 2, '005.1 22', 'Includes index.', 'producing_oss.jpg', NULL, 0, 0, '2007-11-29 16:20:45', '2007-11-29 16:31:21'),
(6, 1, 'PostgreSQL : a comprehensive guide to building, programming, and administering PostgreSQL databases', '1st ed.', '0735712573', 4, 2003, 'xvii, 790 p. : ill. ; 23cm.', 'DeveloperÃ¢â‚¬â„¢s library', '005.75/85-22 Kor p', 'en', NULL, 3, '005.75/85 22', 'PostgreSQL is the world''s most advanced open-source database. PostgreSQL is the most comprehensive, in-depth, and easy-to-read guide to this award-winning database. This book starts with a thorough overview of SQL, a description of all PostgreSQL data types, and a complete explanation of PostgreSQL commands.', 'postgresql.jpg', NULL, 0, 0, '2007-11-29 16:29:33', NOW()),
(7, 1, 'Web application architecture : principles, protocols, and practices', NULL, '0471486566', 5, 2003, 'xi, 357 p. : ill. ; 23 cm.', NULL, '005.7/2-21 Leo w', 'en', NULL, 1, '005.7/2 21', 'An in-depth examination of the core concepts and general principles of Web application development.\r\nThis book uses examples from specific technologies (e.g., servlet API or XSL), without promoting or endorsing particular platforms or APIs. Such knowledge is critical when designing and debugging complex systems. This conceptual understanding makes it easier to learn new APIs that arise in the rapidly changing Internet environment.', 'webapp_arch.jpg', NULL, 0, 0, '2007-11-29 16:41:57', '2007-11-29 16:32:46'),
(8, 1, 'Ajax : creating Web pages with asynchronous JavaScript and XML', NULL, '9780132272674', 6, 2007, 'xxii, 384 p. : ill. ; 24 cm.', 'Bruce PerensÃ¢â‚¬â„¢ Open Source series', '006.7/86-22 Woy a', 'en', NULL, 4, '006.7/86 22', 'Using Ajax, you can build Web applications with the sophistication and usability of traditional desktop applications and you can do it using standards and open source software. Now, for the first time, there''s an easy, example-driven guide to Ajax for every Web and open source developer, regardless of experience.', 'ajax.jpg', NULL, 0, 0, '2007-11-29 16:47:20', NOW()),
(9, 1, 'The organization of information', '2nd ed.', '1563089769', 7, 2004, 'xxvii, 417 p. : ill. ; 27 cm.', 'Library and information science text series', '025-22 Tay o', 'en', NULL, 5, '025 22', 'A basic textbook for students of library and information studies, and a guide for practicing school library media specialists. Describes the impact of global forces and the school district on the development and operation of a media center, the technical and human side of management, programmatic activities, supportive services to students, and the quality and quantity of resources available to support programs.', 'organization_information.jpg', NULL, 0, 0, '2007-11-29 16:54:12', '2007-11-29 16:27:20'),
(10, 1, 'Library and Information Center Management', '7th ed.', '9781591584063', 7, 2007, 'xxviii, 492 p. : ill. ; 27 cm.', 'Library and information science text series', '025.1-22 Stu l', 'en', NULL, 5, '025.1 22', NULL, 'library_info_center.JPG', NULL, 0, 0, '2007-11-29 16:58:51', '2007-11-29 16:27:40'),
(11, 1, 'Information Architecture for the World Wide Web: Designing Large-Scale Web Sites', '2nd ed.', '9780596000356', 2, 2002, '500p.', NULL, '006.7-22 Mor i', 'en', NULL, 6, '006.7 22', 'Information Architecture for the World Wide Web is about applying the principles of architecture and library science to web site design. Each website is like a public building, available for tourists and regulars alike to breeze through at their leisure. The job of the architect is to set up the framework for the site to make it comfortable and inviting for people to visit, relax in, and perhaps even return to someday.', 'information_arch.jpg', NULL, 0, 0, '2007-11-29 17:26:14', '2007-11-29 16:32:25'),
(12, 1, 'Corruption and development', NULL, '9780714649023', 8, 1998, '166 p. : ill. ; 22 cm.', NULL, '364.1 Rob c', 'en', NULL, 7, '364.1/322/091724 21', 'The articles assembled in this volume offer a fresh approach to analysing the problem of corruption in developing countries and the k means to tackle the phenomenon.', 'corruption_development.jpg', NULL, 0, 0, '2007-11-29 17:45:30', '2007-11-29 16:20:53'),
(13, 1, 'Corruption and development : the anti-corruption campaigns', NULL, '0230525504', 9, 2007, '310p.', NULL, '364.1 Bra c', 'en', NULL, 8, '364.1/323091724 22', 'This book provides a multidisciplinary interrogation of the global anti-corruption campaigns of the last ten years, arguing that while some positive change is observable, the period is also replete with perverse consequences and unintended outcomes', 'corruption_development_anti_campaign.jpg', NULL, 0, 0, '2007-11-29 17:49:49', '2007-11-29 16:19:48'),
(14, 1, 'Pigs at the trough : how corporate greed and political corruption are undermining America', NULL, '1400047714', 10, 2003, '275 p. ; 22 cm.', NULL, '364.1323 Huf p', 'en', NULL, 8, '364.1323', NULL, 'pigs_at_trough.jpg', NULL, 0, 0, '2007-11-29 17:56:00', '2007-11-29 16:18:33'),
(15, 1, 'Lords of poverty : the power, prestige, and corruption of the international aid business', NULL, '9780871134691', 11, 1994, 'xvi, 234 p. ; 22 cm.', NULL, '338.9 Han l', 'en', NULL, 8, '338.9/1/091724 20', 'Lords of Poverty is a case study in betrayals of a public trust. The shortcomings of aid are numerous, and serious enough to raise questions about the viability of the practice at its most fundamental levels. Hancocks report is thorough, deeply shocking, and certain to cause critical reevaluation of the governments motives in giving foreign aid, and of the true needs of our intended beneficiaries.', 'lords_of_poverty.jpg', NULL, 0, 0, '2007-11-29 18:08:13', '2007-11-29 16:13:11');


-- --------------------------------------------------------

--
-- Dumping data for table `biblio_author`
--

INSERT IGNORE INTO `biblio_author` (`biblio_id`, `author_id`, `level`) VALUES
(1, 1, 1),
(2, 2, 1),
(2, 3, 2),
(2, 4, 2),
(2, 5, 2),
(2, 6, 2),
(3, 7, 1),
(3, 8, 2),
(4, 9, 1),
(5, 10, 1),
(6, 11, 1),
(6, 12, 2),
(7, 13, 1),
(7, 14, 2),
(8, 15, 1),
(9, 16, 1),
(10, 17, 1),
(10, 18, 2),
(11, 19, 1),
(11, 20, 2),
(12, 21, 1),
(13, 22, 1),
(14, 23, 1),
(15, 24, 1);

-- --------------------------------------------------------

--
-- Dumping data for table `biblio_topic`
--

INSERT IGNORE INTO `biblio_topic` (`biblio_id`, `topic_id`, `level`) VALUES
(1, 1, 1),
(1, 2, 2),
(2, 3, 1),
(2, 4, 2),
(2, 5, 2),
(3, 1, 1),
(3, 6, 2),
(3, 7, 2),
(4, 4, 1),
(4, 8, 2),
(5, 8, 1),
(5, 9, 2),
(6, 1, 1),
(6, 7, 2),
(7, 2, 1),
(7, 10, 2),
(8, 1, 1),
(8, 2, 2),
(9, 11, 1),
(9, 12, 2),
(9, 13, 2),
(10, 11, 1),
(10, 14, 2),
(12, 15, 1),
(12, 16, 2),
(13, 15, 1),
(14, 15, 1),
(15, 15, 1),
(15, 17, 2);

-- --------------------------------------------------------

--
-- Dumping data for table `item`
--

INSERT IGNORE INTO `item` (`item_id`, `biblio_id`, `coll_type_id`, `item_code`, `inventory_code`, `received_date`, `supplier_id`, `order_no`, `location_id`, `order_date`, `item_status_id`, `site`, `source`, `invoice`, `price`, `price_currency`, `invoice_date`, `input_date`, `last_update`) VALUES
(1, 8, 1, 'B00001', 'INV/B00001', NULL, '0', '', 'SL', NULL, '0', '', 1, '', 500000, 'Rupiah', NULL, '2008-12-26 22:11:10', '2008-12-26 22:14:13'),
(2, 6, 1, 'B00002', 'INV/B00002', NULL, '0', '', 'SL', NULL, '0', '', 1, '', 700000, 'Rupiah', NULL, '2008-12-26 22:11:45', '2008-12-26 22:13:45'),
(3, 15, 1, 'B00003', 'INV/B00003', NULL, '0', '', 'SL', NULL, '0', '', 1, '', 300000, 'Rupiah', NULL, '2008-12-26 22:15:09', '2008-12-26 22:15:09'),
(4, 14, 1, 'B00004', 'INV/B00004', NULL, '0', '', 'SL', NULL, '0', '', 1, '', 250000, 'Rupiah', NULL, '2008-12-26 22:15:49', '2008-12-26 22:15:49'),
(5, 13, 1, 'B00005', 'INV/B00005', NULL, '0', '', 'SL', NULL, '0', '', 2, '', 0, NULL, NULL, '2008-12-26 22:17:04', '2008-12-26 22:17:04'),
(6, 12, 1, 'B00006', 'INV/B00006', NULL, '0', '', 'SL', NULL, '0', '', 1, '', 350000, 'Rupiah', NULL, '2008-12-26 22:17:52', '2008-12-26 22:17:52'),
(7, 4, 1, 'B00007', 'INV/B00007', NULL, '0', '', 'SL', NULL, '0', '', 1, '', 450000, 'Rupiah', NULL, '2008-12-26 22:18:29', '2008-12-26 22:18:29'),
(8, 4, 1, 'B00008', 'INV/B00008', NULL, '0', '', 'SL', NULL, '0', '', 2, '', 0, NULL, NULL, '2008-12-26 22:18:51', '2008-12-26 22:18:51'),
(9, 2, 1, 'B00009', 'INV/B00009', NULL, '0', '', 'SL', NULL, '0', '', 1, '', 630000, 'Rupiah', NULL, '2008-12-26 22:19:28', '2008-12-26 22:19:28'),
(10, 2, 1, 'B00010', 'INV/B00010', NULL, '0', '', 'SL', NULL, '0', '', 1, '', 630000, 'Rupiah', NULL, '2008-12-26 22:19:57', '2008-12-26 22:19:57');

-- --------------------------------------------------------

--
-- Dumping data for table `mst_author`
--

INSERT IGNORE INTO `mst_author` (`author_id`, `author_name`, `authority_type`, `input_date`, `last_update`) VALUES
(1, 'Valade, Janet', 'p', '2007-11-29', '2007-11-29'),
(2, 'Siever, Ellen', 'p', '2007-11-29', '2007-11-29'),
(3, 'Love, Robert', 'p', '2007-11-29', '2007-11-29'),
(4, 'Robbins, Arnold', 'p', '2007-11-29', '2007-11-29'),
(5, 'Figgins, Stephen', 'p', '2007-11-29', '2007-11-29'),
(6, 'Weber, Aaron', 'p', '2007-11-29', '2007-11-29'),
(7, 'Kofler, Michael', 'p', '2007-11-29', '2007-11-29'),
(8, 'Kramer, David', 'p', '2007-11-29', '2007-11-29'),
(9, 'Raymond, Eric', 'p', '2007-11-29', '2007-11-29'),
(10, 'Fogel, Karl', 'p', '2007-11-29', '2007-11-29'),
(11, 'Douglas, Korry', 'p', '2007-11-29', '2007-11-29'),
(12, 'Douglas, Susan', 'p', '2007-11-29', '2007-11-29'),
(13, 'Shklar, Leon', 'p', '2007-11-29', '2007-11-29'),
(14, 'Rosen, Richard', 'p', '2007-11-29', '2007-11-29'),
(15, 'Woychowsky, Edmond', 'p', '2007-11-29', '2007-11-29'),
(16, 'Taylor, Arlene G.', 'p', '2007-11-29', '2007-11-29'),
(17, 'Stueart, Robert D.', 'p', '2007-11-29', '2007-11-29'),
(18, 'Moran, Barbara B.', 'p', '2007-11-29', '2007-11-29'),
(19, 'Morville, Peter', 'p', '2007-11-29', '2007-11-29'),
(20, 'Rosenfeld, Louis', 'p', '2007-11-29', '2007-11-29'),
(21, 'Robinson, Mark', 'p', '2007-11-29', '2007-11-29'),
(22, 'Bracking, Sarah', 'p', '2007-11-29', '2007-11-29'),
(23, 'Huffington, Arianna Stassinopoulos', 'p', '2007-11-29', '2007-11-29'),
(24, 'Hancock, Graham', 'p', '2007-11-29', '2007-11-29');

-- --------------------------------------------------------

--
-- Dumping data for table `mst_place`
--

INSERT IGNORE INTO `mst_place` VALUES
(1, 'Hoboken, NJ', '2007-11-29', '2007-11-29'),
(2, 'Sebastopol, CA', '2007-11-29', '2007-11-29'),
(3, 'Indianapolis', '2007-11-29', '2007-11-29'),
(4, 'Upper Saddle River, NJ', '2007-11-29', '2007-11-29'),
(5, 'Westport, Conn.', '2007-11-29', '2007-11-29'),
(6, 'Cambridge, Mass', '2007-11-29', '2007-11-29'),
(7, 'London', '2007-11-29', '2007-11-29'),
(8, 'New York', '2007-11-29', '2007-11-29');

-- --------------------------------------------------------

--
-- Dumping data for table `mst_publisher`
--

INSERT IGNORE INTO `mst_publisher` (`publisher_id`, `publisher_name`, `input_date`, `last_update`) VALUES
(1, 'Wiley', '2007-11-29', '2007-11-29'),
(2, 'OReilly', '2007-11-29', '2007-11-29'),
(3, 'Apress', '2007-11-29', '2007-11-29'),
(4, 'Sams', '2007-11-29', '2007-11-29'),
(5, 'John Wiley', '2007-11-29', '2007-11-29'),
(6, 'Prentice Hall', '2007-11-29', '2007-11-29'),
(7, 'Libraries Unlimited', '2007-11-29', '2007-11-29'),
(8, 'Taylor & Francis Inc.', '2007-11-29', '2007-11-29'),
(9, 'Palgrave Macmillan', '2007-11-29', '2007-11-29'),
(10, 'Crown publishers', '2007-11-29', '2007-11-29'),
(11, 'Atlantic Monthly Press', '2007-11-29', '2007-11-29');

-- --------------------------------------------------------

--
-- Dumping data for table `mst_topic`
--

INSERT IGNORE INTO `mst_topic` (`topic_id`, `topic`, `input_date`, `last_update`) VALUES
(1, 'Programming', '2007-11-29', '2007-11-29'),
(2, 'Website', '2007-11-29', '2007-11-29'),
(3, 'Operating System', '2007-11-29', '2007-11-29'),
(4, 'Linux', '2007-11-29', '2007-11-29'),
(5, 'Computer', '2007-11-29', '2007-11-29'),
(6, 'Database', '2007-11-29', '2007-11-29'),
(7, 'RDBMS', '2007-11-29', '2007-11-29'),
(8, 'Open Source', '2007-11-29', '2007-11-29'),
(9, 'Project', '2007-11-29', '2007-11-29'),
(10, 'Design', '2007-11-29', '2007-11-29'),
(11, 'Information', '2007-11-29', '2007-11-29'),
(12, 'Organization', '2007-11-29', '2007-11-29'),
(13, 'Metadata', '2007-11-29', '2007-11-29'),
(14, 'Library', '2007-11-29', '2007-11-29'),
(15, 'Corruption', '2007-11-29', '2007-11-29'),
(16, 'Development', '2007-11-29', '2007-11-29'),
(17, 'Poverty', '2007-11-29', '2007-11-29');

-- --------------------------------------------------------

