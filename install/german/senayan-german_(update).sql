-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306

-- Generation Time: Aug 20, 2009 at 07:49 PM
-- Server version: 5.1.34
-- PHP Version: 5.2.9-2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tmp_senayan3`
--

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

DROP TABLE IF EXISTS `content`;
CREATE TABLE `content` (
  `content_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content_desc` text COLLATE utf8_unicode_ci NOT NULL,
  `content_path` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL,
  PRIMARY KEY (`content_id`),
  KEY `content_path` (`content_path`),
  FULLTEXT KEY `content_title` (`content_title`),
  FULLTEXT KEY `content_desc` (`content_desc`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`content_id`, `content_title`, `content_desc`, `content_path`, `input_date`, `last_update`) VALUES
(1, 'Über die Bibliothek', '<table style="width: 100%;" border="0" cellspacing="10" cellpadding="5">\r\n<tbody>\r\n<tr>\r\n<td width="50%" valign="top">\r\n<h3>Kontakt</h3>\r\n<p><strong>Adressse:</strong> <br />Beispielbibliothek<br />Beispielgasse 42<br />222391, Hamburg</p>\r\n<p>E-Mail:&nbsp;&nbsp;&nbsp; auskunft@beispielbibliothek<br />Telefon: (040) 123 456<br />Fax: &nbsp; &nbsp; &nbsp; (040) 123 457</p>\r\n</td>\r\n<td width="50%" valign="top">\r\n<h3>&Ouml;ffnungszeiten</h3>\r\n<p><strong>Montag - Freitag:</strong><br />Ge&ouml;ffnet ab: 08:00<br />Ge&ouml;ffnet bis: 20:00<br />Pause: 12:00 - 13:00<br /><br /> <strong>Samstag:</strong> <br />Ge&ouml;ffnet ab : 08:00<br />Ge&ouml;ffnet bis: 17:00<br />Pause: 12:00 - 13:00</p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n<h3>Bestand</h3>\r\n<p>Wir bieten Ihnen ein gro&szlig;es Angebot von Literatur f&uuml;r alle Interessengebiete an. Neben dem gedruckten Wort bieten wir Ihnen aucheine umfassendes Angebot digitaler Medien (CD-ROM, Audio-CD und DVD) an. Und wenn Sie sich &uuml;ber aktuelle Entwicklungen informieren wollen, dann werden Sie sicher in unserem Zeitungs- und Zeitschriftenangebot f&uuml;ndig.</p>\r\n<p>&nbsp;</p>\r\n<h3>Bibliotheksmitgliedschaft</h3>\r\n<p>Sie k&ouml;nnen unser Angebot vor Ort direkt in Anspruch nehmen. Wollen Sie etwas ausleihen, dann lassen Sie sich doch bitte einen Bibliotheksausweis ausstellen. Da wir eine fiktionale Bibliothek sind, sind Ausweis und Ausleihen vollkommen kostenlos f&uuml;r Sie. Einzigst bei &Uuml;berschreitung der Leihfrist k&ouml;nnen Kosten anfallen. Bitte bringen Sie den Personalausweis mit, damit wir Sie schnell bei uns Willkommen hei&szlig;en k&ouml;nnen. Wenn Sie Fragen haben, werden diese gerne von unseren Mitarbeitern beantwortet - vor Ort oder auch per E-Mail</p>', 'libinfo', '2009-08-20 19:00:00', '2009-08-20 19:00:00'),
(2, 'Hilfe zur Suche', '<h3>Suchen</h3>\r\n<p>Ihnen stehen zwei M&ouml;glichkeiten zur Suche in unserem Katalog offen. Die erste ist die <strong>EINFACH SUCHE</strong>. Hier k&ouml;nnen Sie nach Autoren, Titeln und Schlagw&ouml;rtern suchen. Geben Sie einfach einen oder mehrere Suchbegriffe in das Suchfeld ein. Kurz nachdem Sie ihre Suchanfrage abgeschickt haben, erhalten Sie eine Liste von Titeln, die bei uns vorhanden sind. Klicken Sie auf einen Titel um weitere Informationen zu diesem zu erhalten.</p>\r\n<p>Die <strong>ERWEITERTE SUCHE </strong>erm&ouml;glicht Ihnen eine noch gezieltere Suche. Hier wird ausschlie&szlig;lich in den Feldern gesucht f&uuml;r die ein Suchbegriff angegeben ist. Suchen Sie zum Beispiel nach dem Autoren <em>Shakespeare</em>, dann erhalten Sie alle Titel in unserem Katalog, die von Shakespeare verfasst wurden, nicht aber beispielsweise Biographien, die im Titel Shakespeare nennen. Sie k&ouml;nnen aber auch alle Suchfelder frei lassen und zum Beispiel nur nach CD-ROMs suchen. Daf&uuml;r w&auml;hlen Sie einfach den entsprechenden Materialcode und starten die Suche.</p>\r\n<p>&nbsp;</p>\r\n<h3>Nichts gefunden?</h3>\r\n<p>Eine M&ouml;glichkeit ist, dass Sie sich vertippt haben. Ein guter Tipp hierzu ist, dass Sie den Suchbegriff einfach verk&uuml;rzen, insbesondere wenn sie sich bei der korrekten Schreibweise nicht sicher sind. Geben Sie beispielsweise nur <em>Shakespear</em> oder gar nur <em>Shak</em> ein um Shakespeare zu finden. Sie k&ouml;nnten sogar <em>akespear</em> eingeben und w&uuml;rden das Gesuchte finden.</p>\r\n<p>Nun zur anderen M&ouml;glichkeit. Wir versuchen den Bestand bestm&ouml;glich an den Bed&uuml;rfnissen und W&uuml;nschen unserer Nutzer auszurichten. Dennoch kann es nat&uuml;rlich vorkommen, dass wir einen von Ihnen gesuchten Titel einfach nicht besitzen. In diesem Fall schlagen Sie doch einfach die Anschaffung dieses Titels vor. Sprechen Sie dazu einen Bibliotheksmitarbeiter an oder f&uuml;llen Sie einen Erwerbungswunsch aus - Vordrucke hierf&uuml;r liegen an der Ausleihe aus.</p>', 'help', '2009-08-20 19:00:00', '2009-08-20 19:00:00'),
(3, 'Willkommen im Adminbereich', '<table style="width: 100%;" border="0" cellspacing="0" cellpadding="5">\r\n<tbody>\r\n<tr>\r\n<td width="5%" valign="top"><a class="icon biblioIcon" href="?mod=bibliography"></a></td>\r\n<td width="45%" valign="top">\r\n<div class="heading">Katalogisierung</div>\r\nDas Modul Katalogisierung erm&ouml;glicht Ihnen die Verwaltung der bibliographischen Aufnahmen Ihrer Bibliothek. Au&szlig;erdem k&ouml;nnen Sie Exemplare zu den Titelaufnahmen hinzuf&uuml;gen und verwalten. Exemplare k&ouml;nnen dann im Modul Ausleihe entliehen werden.</td>\r\n<td width="5%" valign="top"><a class="icon circulationIcon" href="?mod=circulation"></a></td>\r\n<td width="45%" valign="top">\r\n<div class="heading">Ausleihe</div>\r\nDas Modul Ausleihe erm&ouml;glicht Ihnen die einfache Entleihung und R&uuml;cknahme von Medien. Zus&auml;tzlich k&ouml;nnen Sie Vormerkungen t&auml;tigen, Ausleihregeln definieren und sich einen &Uuml;beblick &uuml;ber Mahngeb&uuml;hren verschaffen.<br /></td>\r\n</tr>\r\n<tr>\r\n<td width="5%" valign="top"><a class="icon memberIcon" href="?mod=membership"></a></td>\r\n<td width="45%" valign="top">\r\n<div class="heading">Mitgliedschaften</div>\r\nDas Modul Mitgliedschaften erm&ouml;glicht Ihnen die Verwaltung der Nutzer ihrer Bibliothek. Sie k&ouml;nnen neue Mitglieder hinzuf&uuml;gen und bestehende aktualisieren oder l&ouml;schen. Au&szlig;erdem k&ouml;nnen Sie Mitgliedstypen festlegen und diesen beispielsweise verschiedene Ausleihlimits zuweisen. Schlie&szlig;lich k&ouml;nnen Sie noch Mitgliederkarten (Ausweise) drucken.<br /><br /></td>\r\n<td width="5%" valign="top"><a class="icon stockTakeIcon" href="?mod=stock_take"></a></td>\r\n<td width="45%" valign="top">\r\n<div class="heading">Inventur</div>\r\nDas Modul Inventur erleichtert Ihnen den Prozess einer Inventur Ihres Bestandes. Probieren Sie es am besten einfach aus.<br /><br /></td>\r\n</tr>\r\n<tr>\r\n<td width="5%" valign="top"><a class="icon masterFileIcon" href="?mod=master_file"></a></td>\r\n<td width="45%" valign="top">\r\n<div class="heading">Master File</div>\r\nDas Modul Master File erm&ouml;glicht es Ihnen insbesondere bei Titelaufnahmen (aber auch anderen Modulen) auf Referenzdaten zur&uuml;ckzugreifen. Unter anderem k&ouml;nnen Sie Autoren, Schlagw&ouml;rter und Herausgeber verwalten. Das verrinngert die Wahrscheinlichkeit von Fehlern in Aufnahmen und an anderen Stellen.<br /></td>\r\n<td width="5%" valign="top"><a class="icon systemIcon" href="?mod=system"></a></td>\r\n<td width="45%" valign="top">\r\n<div class="heading">System</div>\r\nDas Modul System erm&ouml;glicht Ihnen die Konfiguration systemweiter Einstellungen. Au&szlig;erdem k&ouml;nnen Sie unter anderem Barcodes generieren, neue Module und Seiten hinzuf&uuml;gen und Datenbanksicherungen vornehmen.<br /></td>\r\n</tr>\r\n<tr>\r\n<td width="5%" valign="top"><a class="icon reportIcon" href="?mod=reporting"></a></td>\r\n<td width="45%" valign="top">\r\n<div class="heading">Berichte</div>\r\n<p>Das Modul Berichte erm&ouml;glicht Ihnen die Erstellung und den Druck von Berichten zu vielen Bereichen - u.a. zu Mitgliedschaften, Ausleihe und Titelaufnahmen - des Bibliotheksmanagementsystems. Alle Berichte werden absolut aktuell aus der Datenbank generiert.</p>\r\n<br /></td>\r\n<td width="5%" valign="top">&nbsp;</td>\r\n<td style="width: 45%;" valign="top">\r\n<div class="heading">Periodika</div>\r\nDas Modul Periodika erm&ouml;glicht Ihnen die Verwaltung von Periodika (Zeitungen, Zeitschriften). Erstellen Sie zun&auml;chst eine Titelaufnahme und f&uuml;gen Sie dieser dann in diesem Modul einen Kardex hinzu. Sie haben die volle Kontrolle &uuml;ber Ihre Periodikabez&uuml;ge.<br /></td>\r\n</tr>\r\n</tbody>\r\n</table>', 'adminhome', '2009-08-20 19:00:00', '2009-08-20 19:00:00'),
(4, 'Startseiteninfo', '<p>Willkommen auf der Webseite der <strong>Beispielbibliothek</strong>. Nutzen Sie unseren Online Public Access Catalog (OPAC) um unsere Sammlung zu durchsuchen.</p>', 'headerinfo', '2009-08-20 19:00:00', '2009-08-20 19:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `mst_coll_type`
--

DROP TABLE IF EXISTS `mst_coll_type`;
CREATE TABLE `mst_coll_type` (
  `coll_type_id` int(3) NOT NULL AUTO_INCREMENT,
  `coll_type_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`coll_type_id`),
  UNIQUE KEY `coll_type_name` (`coll_type_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Dumping data for table `mst_coll_type`
--

INSERT INTO `mst_coll_type` (`coll_type_id`, `coll_type_name`, `input_date`, `last_update`) VALUES
(1, 'Nachschlagewerke', '2007-11-29', '2009-08-20'),
(2, 'Sachücher', '2007-11-29', '2009-08-20'),
(3, 'Belletristik', '2007-11-29', '2009-08-20');

-- --------------------------------------------------------

--
-- Table structure for table `mst_frequency`
--

DROP TABLE IF EXISTS `mst_frequency`;
CREATE TABLE `mst_frequency` (
  `frequency_id` int(11) NOT NULL AUTO_INCREMENT,
  `frequency` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `language_prefix` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time_increment` smallint(6) DEFAULT NULL,
  `time_unit` enum('day','week','month','year') COLLATE utf8_unicode_ci DEFAULT 'day',
  `input_date` date NOT NULL,
  `last_update` date NOT NULL,
  PRIMARY KEY (`frequency_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

--
-- Dumping data for table `mst_frequency`
--

INSERT INTO `mst_frequency` (`frequency_id`, `frequency`, `language_prefix`, `time_increment`, `time_unit`, `input_date`, `last_update`) VALUES
(1, 'Wöchentlich', 'de', 1, 'week', '2009-05-23', '2009-08-20'),
(2, 'Zweichwöchentlich', 'de', 14, 'day', '2009-05-23', '2009-08-20'),
(3, 'Monatlich', 'de', 1, 'month', '2009-05-23', '2009-08-20'),
(4, 'Zweimonatlich', 'de', 2, 'month', '2009-05-23', '2009-08-20'),
(5, 'Vierteljährlich', 'de', 3, 'month', '2009-05-23', '2009-08-20'),
(6, '3 mal im Jahr', 'de', 4, 'month', '2009-05-23', '2009-08-20'),
(7, 'Jährlich', 'de', 1, 'year', '2009-05-23', '2009-08-20');

-- --------------------------------------------------------

--
-- Table structure for table `mst_gmd`
--

DROP TABLE IF EXISTS `mst_gmd`;
CREATE TABLE `mst_gmd` (
  `gmd_id` int(11) NOT NULL AUTO_INCREMENT,
  `gmd_code` varchar(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `gmd_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `icon_image` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `input_date` date NOT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`gmd_id`),
  UNIQUE KEY `gmd_name` (`gmd_name`),
  UNIQUE KEY `gmd_code` (`gmd_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=33 ;

--
-- Dumping data for table `mst_gmd`
--

INSERT INTO `mst_gmd` (`gmd_id`, `gmd_code`, `gmd_name`, `icon_image`, `input_date`, `last_update`) VALUES
(1, 'TE', 'Text', NULL, '2009-08-16', '2009-08-20'),
(2, 'OK', 'Originalkunstwerk', NULL, '2009-08-16', '2009-08-20'),
(3, 'DG', 'Diagramm', NULL, '2009-08-16', '2009-08-20'),
(4, 'CS', 'Computer Software', NULL, '2009-08-16', '2009-08-20'),
(5, 'DI', 'Diorama', NULL, '2009-08-16', '2009-08-20'),
(6, 'FS', 'Filmstreifen', NULL, '2009-08-16', '2009-08-20'),
(7, 'FK', 'Flashkarte', NULL, '2009-08-16', '2009-08-20'),
(8, 'SP', 'Spiel', NULL, '2009-08-16', '2009-08-20'),
(9, 'GL', 'Globus', NULL, '2009-08-16', '2009-08-20'),
(10, 'BS', 'Bausatz', NULL, '2009-08-16', '2009-08-20'),
(11, 'KA', 'Karte', NULL, '2009-08-16', '2009-08-20'),
(12, 'MF', 'Mikroform', NULL, '2009-08-16', '2009-08-20'),
(13, 'HS', 'Handschrift', NULL, '2009-08-16', '2009-08-20'),
(14, 'MO', 'Modell', NULL, '2009-08-16', '2009-08-20'),
(15, 'MP', 'Film', NULL, '2009-08-16', '2009-08-20'),
(16, 'MS', 'Microscope Slide', NULL, '2009-08-16', '2009-08-20'),
(17, 'MU', 'Musik', NULL, '2009-08-16', '2009-08-20'),
(18, 'BI', 'Bild', NULL, '2009-08-16', '2009-08-20'),
(19, 'PL', 'Primärliteratur (Realien)', NULL, '2009-08-16', '2009-08-20'),
(20, 'DIA', 'Dia', NULL, '2009-08-16', '2009-08-20'),
(21, 'TO', 'Tonaufnahme', NULL, '2009-08-16', '2009-08-20'),
(22, 'TZ', 'Technische Zeichnung', NULL, '2009-08-16', '2009-08-20'),
(23, 'TP', 'Transparent', NULL, '2009-08-16', '2009-08-20'),
(24, 'VA', 'Videoaufnahme', NULL, '2009-08-16', '2009-08-20'),
(25, 'AS', 'Ausstattung', NULL, '2009-08-16', '2009-08-20'),
(26, 'CDA', 'Computerdatei', NULL, '2009-08-16', '2009-08-20'),
(27, 'KM', 'Kartographisches Material', NULL, '2009-08-16', '2009-08-20'),
(28, 'CD', 'CD-ROM', NULL, '2009-08-16', '2009-08-20'),
(29, 'MV', 'Multimedia', NULL, '2009-08-16', '2009-08-20'),
(30, 'ER', 'Elektronische Resource', NULL, '2009-08-16', '2009-08-20'),
(31, 'DVD', 'DVD', NULL, '2009-08-16', '2009-08-20'),
(32, NULL, '', NULL, '2009-08-17', '2009-08-17');

-- --------------------------------------------------------

--
-- Table structure for table `mst_item_status`
--

DROP TABLE IF EXISTS `mst_item_status`;
CREATE TABLE `mst_item_status` (
  `item_status_id` char(3) COLLATE utf8_unicode_ci NOT NULL,
  `item_status_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `rules` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `no_loan` smallint(1) NOT NULL DEFAULT '0',
  `skip_stock_take` smallint(1) NOT NULL DEFAULT '0',
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`item_status_id`),
  UNIQUE KEY `item_status_name` (`item_status_name`)
  KEY `no_loan` (`no_loan`),
  KEY `skip_stock_take` (`skip_stock_take`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `mst_item_status`
--

INSERT INTO `mst_item_status` (`item_status_id`, `item_status_name`, `rules`, `input_date`, `last_update`) VALUES
('R', 'Reparatur', 'a:1:{i:0;s:1:"1";}', 1, 0, '2009-08-16', '2009-08-20'),
('NA', 'Nicht Ausleihbar', 'a:1:{i:0;s:1:"1";}', 1, 0, '2009-08-16', '2009-08-20');

-- --------------------------------------------------------

--
-- Table structure for table `mst_label`
--

DROP TABLE IF EXISTS `mst_label`;
CREATE TABLE `mst_label` (
  `label_id` int(11) NOT NULL AUTO_INCREMENT,
  `label_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `label_desc` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label_image` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `input_date` date NOT NULL,
  `last_update` date NOT NULL,
  PRIMARY KEY (`label_id`),
  UNIQUE KEY `label_name` (`label_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=4 ;

--
-- Dumping data for table `mst_label`
--

INSERT INTO `mst_label` (`label_id`, `label_name`, `label_desc`, `label_image`, `input_date`, `last_update`) VALUES
(1, 'label-new', 'Neuer Titel', 'label-new.png', '2009-08-16', '2009-08-20'),
(2, 'label-favorite', 'Beliebter Titel', 'label-favorite.png', '2009-08-16', '2009-08-20'),
(3, 'label-multimedia', 'Multimedia', 'label-multimedia.png', '2009-08-16', '2009-08-20');

-- --------------------------------------------------------

--
-- Table structure for table `mst_language`
--

DROP TABLE IF EXISTS `mst_language`;
CREATE TABLE `mst_language` (
  `language_id` char(5) COLLATE utf8_unicode_ci NOT NULL,
  `language_name` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `input_date` date DEFAULT NULL,
  `last_update` date DEFAULT NULL,
  PRIMARY KEY (`language_id`),
  UNIQUE KEY `language_name` (`language_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `mst_language`
--

INSERT INTO `mst_language` (`language_id`, `language_name`, `input_date`, `last_update`) VALUES
('id', 'Indonesisch', '2009-08-16', '2009-08-20'),
('en', 'Englisch', '2009-08-16', '2009-08-20'),
('de', 'Deutsch', '2009-08-20', '2009-08-20');

-- --------------------------------------------------------

--
-- Table structure for table `mst_location`
--

DROP TABLE IF EXISTS `mst_location`;
CREATE TABLE `mst_location` (
  `location_id` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `location_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `input_date` date NOT NULL,
  `last_update` date NOT NULL,
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `location_name` (`location_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `mst_location`
--

INSERT INTO `mst_location` (`location_id`, `location_name`, `input_date`, `last_update`) VALUES
('SL', 'Beispielbibliothek', '2009-08-16', '2009-08-20');

-- --------------------------------------------------------

--
-- Table structure for table `mst_module`
--

DROP TABLE IF EXISTS `mst_module`;
CREATE TABLE `mst_module` (
  `module_id` int(3) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `module_path` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `module_desc` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `module_name` (`module_name`,`module_path`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=DYNAMIC AUTO_INCREMENT=9 ;

--
-- Dumping data for table `mst_module`
--

INSERT INTO `mst_module` (`module_id`, `module_name`, `module_path`, `module_desc`) VALUES
(1, 'katalogisierung', 'bibliography', 'Verwalten Sie Titelaufnahmen und Exemplare'),
(2, 'ausleihe', 'circulation', 'Ermögicht Buchungsvorgänge wie Ausleihe und Rücknahme'),
(3, 'mitgliedschaften', 'membership', 'Verwalten Sie Bibliotheksmitglieder und Mitgliedstypen'),
(4, 'master_file', 'master_file', 'Verwalten Sie Referenzdaten, die von anderen Modulen verwendet werden'),
(5, 'Inventur', 'stock_take', 'Erleichtern Sie sich das Leben bei Bestandsaufnahmen'),
(6, 'system', 'system', 'Konfigurieren Sie Systemeinstellungen, Benutzer und Datenbanksicherungen'),
(7, 'berichte', 'reporting', 'Echtzeitberichte zu Bibliothekssammlungen, Ausleihe und vielem mehr'),
(8, 'periodika', 'serial_control', 'Verwalten Sie ihre Periodikaabonnements');

-- --------------------------------------------------------

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
CREATE TABLE `setting` (
  `setting_id` int(3) NOT NULL AUTO_INCREMENT,
  `setting_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_name` (`setting_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=14 ;

--
-- Dumping data for table `setting`
--

INSERT INTO `setting` (`setting_id`, `setting_name`, `setting_value`) VALUES
(1, 'library_name', 's:26:"Senayan Beispielbibliothek";'),
(2, 'library_subname', 's:39:"Open Source Bibliotheksmanagementsystem";'),
(3, 'template', 'a:2:{s:5:"theme";s:7:"default";s:3:"css";s:26:"template/default/style.css";}'),
(4, 'admin_template', 'a:2:{s:5:"theme";s:7:"default";s:3:"css";s:32:"admin_template/default/style.css";}'),
(5, 'default_lang', 's:5:"de_DE";'),
(6, 'opac_result_num', 's:2:"10";'),
(7, 'enable_promote_titles', 'a:1:{i:0;s:1:"1";}'),
(8, 'quick_return', 'b:1;'),
(9, 'loan_limit_override', 'b:0;'),
(10, 'enable_xml_detail', 'b:1;'),
(11, 'enable_xml_result', 'b:1;'),
(12, 'allow_file_download', 'b:1;'),
(13, 'session_timeout', 's:4:"7200";');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
