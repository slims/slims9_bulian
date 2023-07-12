<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2020-01-12 08:36
 * @File name           : Upgrade.inc.php
 */

namespace Install;


class Upgrade
{

  /**
   * @var SLiMS
   */
  public $slims;

  /**
   * Latest version
   *
   * @var int
   */
  private $version = 34;

  /**
   * @param SLiMS $slims
   * @return Upgrade
   */
  static function init(SLiMS $slims)
  {
    $upgrade = new Upgrade();
    $upgrade->slims = $slims;
    return $upgrade;
  }

  function hookBeforeUpgrade() {
      // store old sql mode
      $old_sql_mode = $this->slims->storeOldSqlMode();
      // update sql_mode to modify database
      $new_sql_mode = str_replace(['NO_ZERO_DATE', 'NO_ZERO_IN_DATE'], '', $old_sql_mode);
      $this->slims->updateSqlMode($new_sql_mode);
  }

  function hookAfterUpgrade($version) {
      // cek if table not exist
      $tables = require 'tables.php';
      foreach ($tables as $table) {
          $mtables = $this->slims->getTables();
          if (!in_array($table['table'], $mtables)) {
              // create table
              $msg = $this->slims->createTable($table);
              if ($msg) $error[] = $msg;
          } else {
              // check column
              foreach ($table['column'] as $column) {
                  $mColumn = $this->slims->getColumn($table['table']);
                  if (!in_array($column['field'], $mColumn)) {
                      $msg = $this->slims->addColumn($table['table'], $column);
                      if ($msg) $error[] = $msg;
                  }
              }
          }
      }

      // make sure use default template
      $this->slims->updateTheme('default', $version);

      // update storeage engine
      $this->slims->updateStorageEngine();

      // rollback sql_mode
      $this->slims->rollbackSqlMode();
  }

  /**
   * Function to execute upgrade role from specific version
   *
   * @param $version
   * @return array
   */
  function from($version)
  {
    // run before script
    $this->hookBeforeUpgrade();

    $raw_err = [];
    for ($i = ($version + 1); $i <= $this->version; $i++) {
      $method = 'upgrade_role_' . $i;
      if (method_exists($this, $method)) {
        $raw_err[] = $this->$method();
      }
    }
    $err = [];
    foreach ($raw_err as $e) {
      if (is_array($e)) {
        foreach ($e as $_e) {
          $err[] = $_e;
        }
      }
    }

    // run after script
    $this->hookAfterUpgrade($version);
    return $err;
  }

  function getVersion()
  {
    return $this->version;
  }

  /**
   * Upgrade role to v3.3.0
   */
  function upgrade_role_1()
  {
  }

  /**
   * Upgrade role to v3.4.0
   */
  function upgrade_role_2()
  {
  }

  /**
   * Upgrade role to v3.5.0
   */
  function upgrade_role_3()
  {
  }

  /**
   * Upgrade role to v3.6.0
   */
  function upgrade_role_4()
  {
  }

  /**
   * Upgrade role to v3.7.0
   */
  function upgrade_role_5()
  {
  }

  /**
   * Upgrade role to v3.8.0
   */
  function upgrade_role_6()
  {
  }

  /**
   * Upgrade role to v3.9.0
   */
  function upgrade_role_7()
  {
  }

  /**
   * Upgrade role to v3.10.0
   */
  function upgrade_role_8()
  {
  }

  /**
   * Upgrade role to v3.11.0
   *
   * @return mixed
   */
  function upgrade_role_9()
  {
    $sql['truncate'][] = "TRUNCATE TABLE `setting`;";

    $sql['insert'][] = "INSERT INTO `setting` (`setting_id`, `setting_name`, `setting_value`) VALUES
(1, 'library_name', 's:7:\"Senayan\";'),
(2, 'library_subname', 's:37:\"Open Source Library Management System\";'),
(3, 'template', 'a:2:{s:5:\"theme\";s:7:\"default\";s:3:\"css\";s:26:\"template/default/style.css\";}'),
(4, 'admin_template', 'a:2:{s:5:\"theme\";s:7:\"default\";s:3:\"css\";s:32:\"admin_template/default/style.css\";}'),
(5, 'default_lang', 's:5:\"en_US\";'),
(6, 'opac_result_num', 's:2:\"10\";'),
(7, 'enable_promote_titles', 'N;'),
(8, 'quick_return', 'b:1;'),
(9, 'allow_loan_date_change', 'b:0;'),
(10, 'loan_limit_override', 'b:0;'),
(11, 'enable_xml_detail', 'b:1;'),
(12, 'enable_xml_result', 'b:1;'),
(13, 'allow_file_download', 'b:1;'),
(14, 'session_timeout', 's:4:\"7200\";');";

    $sql['alter'][] = "ALTER TABLE `mst_item_status` ADD `no_loan` SMALLINT( 1 ) NOT NULL DEFAULT '0' AFTER `rules`, ADD INDEX ( no_loan );
ALTER TABLE `mst_item_status` ADD `skip_stock_take` SMALLINT( 1 ) NOT NULL DEFAULT '0' AFTER `no_loan`, ADD INDEX ( skip_stock_take );
UPDATE `mst_item_status` SET `no_loan`=1 WHERE `rules` LIKE '%s:1:\"1\";%';
UPDATE `mst_item_status` SET `skip_stock_take`=1 WHERE `rules` LIKE '%s:1:\"2\";%';";

    $sql['alter'][] = "ALTER TABLE `biblio` CHANGE `labels` `labels` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;";

    return $this->slims->query($sql, ['truncate', 'alter', 'insert']);
  }

  /**
   * Upgrade role to v3.12.0
   */
  function upgrade_role_10()
  {
  }

  /**
   * Upgrade role to v3.13.0
   *
   * @return mixed
   */
  function upgrade_role_11()
  {
    $sql['insert'][] = "INSERT INTO `setting` (`setting_id`, `setting_name`, `setting_value`) VALUES (NULL, 'circulation_receipt', 'b:1;'),
(NULL, 'barcode_encoding', 's:4:\"128B\";');";

    return $this->slims->query($sql, ['insert']);
  }

  /**
   * Upgrade role to v3.14.0
   */
  function upgrade_role_12()
  {
  }

  /**
   * Upgrade role to v3.15.0
   *
   * @return mixed
   */
  function upgrade_role_13()
  {
    $sql['drop'][] = "DROP TABLE IF EXISTS `search_biblio`;";

    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `search_biblio` (
  `biblio_id` int(11) NOT NULL,
  `title` text COLLATE utf8_unicode_ci,
  `edition` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isbn_issn` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author` text COLLATE utf8_unicode_ci,
  `topic` text COLLATE utf8_unicode_ci,
  `gmd` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `publisher` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `publish_place` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `language` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `classification` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `spec_detail_info` text COLLATE utf8_unicode_ci,
  `location` text COLLATE utf8_unicode_ci,
  `publish_year` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8_unicode_ci,
  `series_title` text COLLATE utf8_unicode_ci,
  `items` text COLLATE utf8_unicode_ci,
  `collection_types` text COLLATE utf8_unicode_ci,
  `call_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `opac_hide` smallint(1) NOT NULL DEFAULT '0',
  `promoted` smallint(1) NOT NULL DEFAULT '0',
  `labels` text COLLATE utf8_unicode_ci,
  `collation` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `image` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `input_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  UNIQUE KEY `biblio_id` (`biblio_id`),
  KEY `add_indexes` (`gmd`,`publisher`,`publish_place`,`language`,`classification`,`publish_year`,`call_number`),
  KEY `add_indexes2` (`opac_hide`,`promoted`),
  FULLTEXT `title` (`title`),
  FULLTEXT `author` (`author`),
  FULLTEXT `topic` (`topic`),
  FULLTEXT `location` (`location`),
  FULLTEXT `items` (`items`),
  FULLTEXT `collection_types` (`collection_types`),
  FULLTEXT `labels` (`labels`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='index table for advance searching technique for SLiMS';";

    $sql['alter'][] = "ALTER TABLE `biblio` CHANGE `publish_year` `publish_year` VARCHAR( 20 ) NULL DEFAULT NULL;";

    $sql['alter'][] = "ALTER TABLE `member` ADD `member_mail_address` varchar(255) collate utf8_unicode_ci default NULL AFTER `member_address`;";

    $sql['alter'][] = "ALTER TABLE `mst_author` DROP INDEX `author_name`;";

    $sql['alter'][] = "ALTER TABLE `mst_author` ADD `author_year` VARCHAR( 20 ) NULL DEFAULT NULL AFTER `author_name`;";

    $sql['alter'][] = "ALTER TABLE `mst_author` ADD UNIQUE (`author_name` ,`authority_type`);";

    $sql['alter'][] = "ALTER TABLE `mst_topic` DROP INDEX `topic`;";

    $sql['alter'][] = "ALTER TABLE `mst_topic` ADD UNIQUE (`topic` ,`topic_type`);";

    $sql['alter'][] = "ALTER TABLE `content` DROP INDEX `content_path`;";

    $sql['alter'][] = "ALTER TABLE `content` ADD UNIQUE (`content_path`);";

    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `member_custom` (
`member_id` VARCHAR(20) NOT NULL ,
PRIMARY KEY ( `member_id` )
) ENGINE = MYISAM COMMENT = 'one to one relation with real member table';";

    $sql['alter'][] = "ALTER TABLE `content` ADD `content_ownpage` ENUM( '1', '2' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '1';";

    $sql['insert'][] = "INSERT INTO `content` (`content_id`, `content_title`, `content_desc`, `content_path`, `input_date`, `last_update`, `content_ownpage`) VALUES
(null, 'Tentang SLiMS', '<p><!--intro_awal--><strong>SLiMS</strong> adalah akronim dari Senayan Library Management System. Awalnya dikembangkan oleh Perpustakaan Kementerian Pendidikan Nasional untuk menggantikan Alice (http://www2.softlinkint.com). Tujuan utamanya agar Perpustakaan Kemdiknas mempunyai kebebasan untuk menggunakan, mempelajari, memodifikasi dan mendistribusikan perangkat lunak yang digunakan. SLiMS, maka dirilis dengan lisensi GPL dan sekarang pengembangan SLiMS dilakukan oleh komunitas penggunanya.<!--intro_akhir--></p>\r\n<p><strong>Asal Mula</strong></p>\r\n<p>Setelah beroperasi 50 tahun lebih, karena beberapa alasan Perpustakaan BC Indonesia yang telah selama bertahun-tahun menjadi andalan layanan BC di Indonesia harus ditutup. Pengelola BC Indonesia kemudian berinisiatif untuk menghibahkan pengelolaan aset perpustakaanya ke tangan institusi pemerintah. Dalam hal ini, institusi pemerintah yang dianggap sesuai bidangnya dan strategis tempatnya, adalah Departemen Pendidikan Nasional (Depdiknas). Yang dihibahkan tidak hanya koleksi, tetapi juga rak koleksi, hardware (server dan workstation) serta sistem termasuk untuk aplikasi manajemen administrasi perpustakaan (Alice).</p>\r\n<p>Seiring dengan berjalannya waktu, manajemen Perpustakaan Depdiknas mulai menghadapi beberapa kendala dalam penggunaan sistem Alice. Pertama, keterbatasan dalam menambahkan fitur-fitur baru. Antara lain: kebutuhan manajemen serial, meng-online-kan katalog di web dan kustomisasi report yang sering berubah-ubah kebutuhannya. Penambahan fitur jika harus meminta modul resmi dari developer Alice, berarti membutuhkan dana tambahan yang tidak kecil. Apalagi tidak ada distributor resminya di Indonesia sehingga harus mengharapkan support dari Inggris. Ditambah lagi beberapa persyaratan yang membutuhkan infrastruktur biaya mahal seperti dedicated public IP agar bisa meng-online-kan Alice di web.<br /><br />Saat itu untuk mengatasi sebagian kebutuhan (utamanya kustomisasi report), dilakukan dengan ujicoba mengakses langsung database yang disimpan dalam format DBase. Terkadang berhasil terkadang tidak karena struktur datanya proprietary dan kompleks serta jumlah rekodnya banyak. Untuk mempelajari struktur database, dicoba melakukan kontak via email ke developer Alice. Tetapi tidak ada respon sama sekali. Disini muncul masalah kedua. Sulitnya mempelajari lebih mendalam cara kerja perangkat lunak Alice. Karena Alice merupakan sistem proprietary yang serba tertutup, segala sesuatunya sangat tergantung vendor. Dibutuhkan sejumlah uang untuk mendapatkan layanan resmi untuk kustomisasi.<br /><br />Perpustakaan Depdiknas salah satu tupoksinya adalah melakukan koordinasi pengelolaan perpustakaan unit kerja dibawah lingkungan Depdiknas. Dalam implementasinya, seringkali muncul kebutuhan untuk bisa mendistribusikan perangkat lunak sistem perpustakaan ke berbagai unit kerja tersebut. Disini masalah ketiga: sulit (atau tidak mungkin) untuk melakukan redistribusi sistem Alice. Alice merupakan perangkat lunak yang secara lisensi tidak memungkinkan diredistribusi oleh pengelola Perpustakaan Depdiknas secara bebas. Semuanya harus ijin dan membutuhkan biaya.<br /><br />November 2006, perpustakaan dihadapkan oleh sebuah masalah mendasar. Sistem Alice tiba-tiba tidak bisa digunakan. Ternyata Alice yang digunakan selama ini diimplementasikan dengan sistem sewa. Pantas saja biayanya relatif murah. Tiap tahun pengguna harus membayar kembali untuk memperpanjang masa sewa pakainya. Tetapi yang mengkhawatirkan adalah fakta bahwa perpustakaan harus menyimpan semua informasi penting dan kritikal di sebuah sistem yang tidak pernah dimiliki. Yang kalau lupa atau tidak mau membayar sewa lagi, hilanglah akses terhadap data kita sendiri. Konyol sekali. Itu sama saja dengan bunuh diri kalau masih tergantung dengan sistem berlisensi seperti itu.<br /><br />Akhirnya pengelola Perpustakaan Depdiknas me-review kembali penggunaan sistem Alice di perpustakaan Depdiknas. Beberapa poin pentingnya antara lain:<br />&bull;&nbsp;&nbsp;&nbsp; Alice memang handal (reliable), tapi punya banyak keterbatasan. Biaya sewanya memang relatif murah, tetapi kalau membutuhkan support tambahan, baik sederhana ataupun kompleks, sangat tergantung dengan developer Alice yang berpusat di Inggris. Butuh biaya yang kalau di total juga tidak murah.<br />&bull;&nbsp;&nbsp;&nbsp; Model lisensi proprietary yang digunakan developer Alice tidak cocok dengan kondisi kebanyakan perpustakaan di Indonesia. Padahal pengelola Perpustakaan Depdiknas sebagai koordinator banyak perpustakaan di lingkungan Depdiknas, punya kepentingan untuk bisa dengan bebas melakukan banyak hal terhadap software yang digunakan.<br />&bull;&nbsp;&nbsp;&nbsp; Menyimpan data penting dan kritikal untuk operasional perpustakaan di suatu software yang proprietary dan menggunakan sistem sewa, dianggap sesuatu yang konyol dan mengancam independensi dan keberlangsungan perpustakaan itu sendiri.<br />&bull;&nbsp;&nbsp;&nbsp; Alice berjalan diatas sistem operasi Windows yang juga proprietary padahal pengelola Perpustakaan Depdiknas ingin beralih menggunakan Sistem Operasi open source (seperti GNU/Linux dan FreeBSD).<br />&bull;&nbsp;&nbsp;&nbsp; Masalah devisa negara yang terbuang untuk membayar software yang tidak pernah dimiliki.<br />&bull;&nbsp;&nbsp;&nbsp; Intinya: pengelola Perpustakaan Depdiknas ingin menggunakan software yang memberikan dan menjamin kebebasan untuk: menggunakan, mempelajari, memodifikasi dan melakukan redistribusi. Lisensi Alice tidak memungkinkan untuk itu.<br /><br />Setelah memutuskan untuk hijrah menggunakan sistem yang lain, maka langkah berikutnya adalah mencari sistem yang ada untuk digunakan atau mengembangkan sendiri sistem yang dibutuhkan. Beberapa pertimbangan yang harus dipenuhi:<br />&bull;&nbsp;&nbsp;&nbsp; Dirilis dibawah lisensi yang menjamin kebebasan untuk: menggunakan, mempelajari, memodifikasi dan melakukan redistribusi. Model lisensi open source (www.openosurce.org) dianggap sebagai model yang paling ideal dan sesuai.<br />&bull;&nbsp;&nbsp;&nbsp; Teknologi yang digunakan untuk membangun sistem juga harus berlisensi open source.<br />&bull;&nbsp;&nbsp;&nbsp; Teknologi yang digunakan haruslah teknologi yang relatif mudah dipelajari oleh pengelola perpustakaan Depdiknas yang berlatarbelakang pendidiknas pustakawan, seperti PHP (scripting language) dan MySQL (database). Jika tidak menguasai sisi teknis teknologi, maka akan terjebak kembali terhadap ketergantungan pada developer.<br /><br />Langkah berikutnya adalah melakukan banding software sistem perpustakaan open source yang bisa diperoleh di internet. Beberapa software yang dicoba antara lain: phpMyLibrary, OpenBiblio, KOHA, EverGreen. Pengelola perpustakaan Depdiknas merasa tidak cocok dengan software yang ada, dengan beberapa alasan:<br />&bull;&nbsp;&nbsp;&nbsp; Desain aplikasi dan database yang tidak baik atau kurang menerapkan secara serius prinsip-prinsip pengembangan aplikasi dan database yang baik sesuai dengan teori yang ada (PHPMyLibrary, OpenBiblio).<br />&bull;&nbsp;&nbsp;&nbsp; Menggunakan teknologi yang sulit dikuasai oleh pengelola perpustakaan Depdiknas (KOHA dan EverGreen dikembangkan menggunakan Perl dan C++ Language yang relatif lebih sulit dipelajari).<br />&bull;&nbsp;&nbsp;&nbsp; Beberapa sudah tidak aktif atau lama sekali tidak di rilis versi terbarunya (PHPMyLibrary dan OpenBiblio).<br /><br />Karena tidak menemukan sistem yang dibutuhkan, maka diputuskan untuk mengembangkan sendiri aplikasi sistem perpustakaan yang dibutuhkan. Dalam dunia pengembangan software, salah satu best practice-nya adalah memberikan nama kode (codename) pengembangan. Nama kode berbeda dengan nama aplikasinya itu sendiri. Nama kode biasanya berbeda-beda tiap versi. Misalnya kode nama &ldquo;Hardy Heron&rdquo; untuk Ubuntu Linux 8.04 dan &ldquo;Jaunty Jackalope&rdquo; untuk Ubuntu Linux 9.04. Pengelola perpustakaan Depdiknas Untuk versi awal (1.0) aplikasi yang akan dikembangkan, memberikan nama kode &ldquo;Senayan&rdquo;. Alasannya sederhana, karena awal dikembangkan di perpustakaan Depdiknas yang berlokasi di Senayan. Apalagi Perpustakaan Depdiknas mempunyai brand sebagai library@senayan. Belakangan karena dirasa nama &ldquo;Senayan&rdquo; dirasa cocok dan punya nilai marketing yang bagus, maka nama &ldquo;Senayan&rdquo; dijadikan nama resmi aplikasi sistem perpustakaan yang dikembangkan.<br /><br />Mengembangkan Senayan<br /><br />Sebelum mulai mengembangkan Senayan, ada beberapa keputusan desain aplikasi yang harus dibuat. Aspek desain ini penting diantaranya untuk pengambilankeputusan dari berbagai masukan yang datang dari komunitas. Antara lain:<br /><br />Pertama,&nbsp; Senayan adalah aplikasi untuk kebutuhan administrasi dan konten perpustakaan (Library Automation System). Senayan didesain untuk kebutuhan skala menengah maupun besar. Cocok untuk perpustakaan yang memiliki koleksi, anggota dan staf banyak di lingkungan jaringan, baik itu lokal (intranet) dan internet.<br /><br />Kedua, Senayan dibangun dengan memperhatikan best practice dalam pengembangan software seperti dalam hal penulisan source code, dokumentasi, dan desain database.<br /><br />Ketiga, Senayan dirancang untuk compliant dengan standar pengelolaan koleksi di perpustakaan. Untuk standar pengatalogan minimal memenuhi syarat AACR 2 level 2 (Anglo-American Cataloging Rules). Kebutuhan untuk kesesuaian dengan standar di perpustakaan terus berkembang dan pengelola perpustakaan Depdiknas dan developer Senayan berkomitmen untuk terus mengembangkan Senayan agar mengikuti standar-standar tersebut.<br /><br />Keempat, Senayan didesain agar bisa juga menjadi middleware bagi aplikasi lain untuk menggunakan data yang ada didalam Senayan. Untuk itu Senayan akan menyediakan API (application programming Interface) yang berbasis web service.<br /><br />Kelima, Senayan merupakan aplikasi yang cross-platform, baik dari sisi aplikasinya itu sendiri dan akses terhadap aplikasi. Untuk itu basis yang paling tepat ada basis web.<br /><br />Keenam, teknologi yang digunakan untuk membangun Senayan, haruslah terbukti bisa diinstall di banyak platform sistem operasi, berlisensi open source dan mudah dipelajari oleh pengelola perpustakaan Depdiknas. Diputuskan untuk menggunakan PHP (www.php.net) untuk web scripting languange dan MySQL (www.mysql.com) untuk server database.<br /><br />Ketujuh, diputuskan untuk mengembangkan library PHP sendiri yang didesain spesifik untuk kebutuhan membangun library automation system. Tidak menggunakan library PHP yang sudah terkenal seperti PEAR (pear.php.net) karena alasan penguasaan terhadap teknologi dan kesederhanaan. Library tersebut diberinama &ldquo;simbio&rdquo;.<br /><br />Kedelapan, untuk mempercepat proses pengembangan, beberapa modul atau fungsi yang dibutuhkan yang dirasa terlalu lama dan rumit untuk dikembangkan sendiri, akan menggunakan software open source yang berlisensi open source juga. Misalnya: flowplayer untuk dukungan multimedia, jQuery untuk dukungan AJAX (Asynchronous Javascript and XML), genbarcode untuk dukungan pembuatan barcode, PHPThumb untuk dukungan generate image on-the-fly, tinyMCE untuk web-based text editor, dan lain-lain.<br /><br />Kesembilan, untuk menjaga spirit open source, proses pengembangan Senayan dilakukan dengan infrastruktur yang berbasis open source. Misalnya: server web menggunakan Apache, server produksi menggunakan OS Linux Centos dan OpenSuse, para developer melakukan pengembangan dengan OS Ubuntu Linux, manajemen source code menggunakan software git, dan lain-lain.<br /><br />Kesepuluh, Senayan dirilis ke masyarakat umum dengan lisensi GNU/GPL versi 3 yang menjamin kebebasan penggunanya untuk mempelajari, menggunakan, memodifikasi dan redistribusi Senayan.<br /><br />Kesebelas, para developer dan pengelola perpustakaan Depdiknas berkomitmen untuk terus mengembangkan Senayan dan menjadikannya salah satu contoh software perpustakaan yang open source, berbasis di indonesia dan menjadi salah satu contoh bagi model pengembangan open source yang terbukti berjalan dengan baik.<br /><br />Keduabelas, model pengembangan Senayan adalah open source yang artinya setiap orang dipersilahkan memberikan kontribusinya. Baik dari sisi pemrogaman, template, dokumentasi, dan lain-lain. Tentu saja ada mekanisme mana kontribusi yang bagus untuk dimasukkan dalam rilis resmi, mana yang tidak. Mengacu ke dokumen &hellip; (TAMBAHKAN DENGAN TULISAN ERIC S RAYMOND)<br /><br />Model pengembangan senayan<br /><br />Pengembangan Senayan awalnya diinisiasi oleh pengelola Perpustakaan Depdiknas. Tetapi sekarang komunitas pengembang Senayan (Senayan Developer Community) yang lebih banyak mengambil peran dalam mengembangkan Senayan. Beberapa hal dibawah ini merupakan kultur yang dibangun dalam mengembangkan Senayan:<br />1.&nbsp;&nbsp;&nbsp; Meritokrasi. Siapa saja bisa berkontribusi. Mereka yang banyak memberikan kontribusi, akan mendapatkan privilege lebih dibandingkan yang lain.<br />2.&nbsp;&nbsp;&nbsp; Minimal punya concern terhadap pengembangan perpustakaan. Contoh lain: berlatar belakang pendidikan ilmu perpustakaan dan informasi, bekerja di perpustakaan, mengelola perpustakaan, dan lain-lain. Diharapkan dengan kondisi ini, sense of librarianship melekat di tiap developer/pengguna Senayan. Sejauh ini, semua developer senayan merupakan pustakawan atau berlatarbelakang pendidikan kepustakawanan (Information and Librarianship).<br />3.&nbsp;&nbsp;&nbsp; Release early, release often, and listen to your customer. Release early artinya setiap perbaikan dan penambahan fitur, secepat mungkin dirilis ke publik. Diharapkan bugs yang ada, bisa cepat ditemukan oleh komunitas, dilaporkan ke developer, untuk kemudian dirilis perbaikannya. Release often, artinya sesering mungkin memberikan update perbaikan bugs dan penambahan fitur. Ini &ldquo;memaksa&rdquo; developer Senayan untuk terus kreatif menambahkan fitur Senayan. Release often juga membuat pengguna berkeyakinan bahwa Senayan punya sustainability yang baik dan terus aktif dikembangkan. Selain itu, release often juga mempunyai dampak pemasaran. Pengguna dan calon pengguna, selalu diingatkan tentang keberadaan Senayan. Tentunya dengan cara yang elegan, yaitu rilis-rilis Senayan. Sejak dirilis ke publi pertama kali November 2007 sampai Juli 2009 (kurang lebih 20 bulan) telah dirilis 18 rilis resmi Senayan. Listen to your customer. Developer Senayan selalu berusaha mengakomodasi kebutuhan pengguna baik yang masuk melalui report di mailing list, ataupun melalui bugs tracking system. Tentu tidak semua masukan diakomodasi, harus disesuaikan dengan desain dan roadmap pengembangan Senayan.<br />4.&nbsp;&nbsp;&nbsp; Dokumentasi. Developer Senayan meyakini pentingnya dokumentasi yang baik dalam mensukseskan implementasi Senayan dibanyak tempat. Karena itu pengembang Senayan mempunyai tim khusus yang bertanggungjawab yang mengembangkan dokumentasi Senayan agar terus uo-to-date mengikuti rilis terbaru.<br />5.&nbsp;&nbsp;&nbsp; Agar ada percepatan dalam pengembangan dan untuk mengakrabkan antar pengembang Senayan, minimal setahun sekali diadakan Senayan Developers Day yang mengumpulkan para developer Senayan dari berbagai kota, dan melakukan coding bersama-sama.<br />Fitur Senayan<br />Sebagai sebuah Sistem Automasi Perpustakaan yang terintergrasi, modul-modul yang telah terdapat di SENAYAN adalah sebagai berikut:<br />Modul Pengatalogan (Cataloging Module)<br />1)&nbsp;&nbsp;&nbsp; Compliance dengan standar AACR2 (Anglo-American Cataloging Rules).<br />2)&nbsp;&nbsp;&nbsp; Fitur untuk membuat, mengedit, dan menghapus data bibliografi sesuai dengan standar deskripsi bibliografi AACR2 level ke dua.<br />3)&nbsp;&nbsp;&nbsp; Mendukung pengelolaan koleksi dalam berbagai macam format seperti monograph, terbitan berseri, audio visual, dsb.<br />4)&nbsp;&nbsp;&nbsp; Mendukung penyimpanan data bibliografi dari situs di Internet.<br />5)&nbsp;&nbsp;&nbsp; Mendukung penggunaan Barcode.<br />6)&nbsp;&nbsp;&nbsp; Manajemen item koleksi untuk dokumen dengan banyak kopi dan format yang berbeda.<br />7)&nbsp;&nbsp;&nbsp; Mendukung format XML untuk pertukaran data dengan menggunakan standar metadata MODS (Metadata Object Description Schema).<br />8)&nbsp;&nbsp;&nbsp; Pencetakan Barcode item/kopi koleksi Built-in.<br />9)&nbsp;&nbsp;&nbsp; Pencetakan Label Punggung koleksi Built-in.<br />10)&nbsp;&nbsp;&nbsp; Pengambilan data katalog melalui protokol Z3950 ke database koleksi Library of Congress.<br />11)&nbsp;&nbsp;&nbsp; Pengelolaan koleksi yang hilang, dalam perbaikan, dan rusak serta pencatatan statusnya untuk dilakukan pergantian/perbaikan terhadap koleksi.<br />12)&nbsp;&nbsp;&nbsp; Daftar kendali untuk pengarang (baik pengarang orang, badan/lembaga, dan pertemuan) sebagai standar konsistensi penuliasn<br />13)&nbsp;&nbsp;&nbsp; Pengaturan hak akses pengelolaan data bibliografi hanya untuk staf yang berhak.<br /><br />Modul Penelusuran (OPAC/Online Public Access catalog Module)<br />1)&nbsp;&nbsp;&nbsp; Pencarian sederhana.<br />2)&nbsp;&nbsp;&nbsp; Pencarian tingkat lanjut (Advanced).<br />3)&nbsp;&nbsp;&nbsp; Dukungan penggunaan Boolean''s Logic dan implementasi CQL (Common Query Language).<br />4)&nbsp;&nbsp;&nbsp; OPAC Web Services berbasis XML.<br />5)&nbsp;&nbsp;&nbsp; Mendukung akses OPAC melalui peralatan portabel (mobile device)<br />6)&nbsp;&nbsp;&nbsp; Menampilkan informasi lengkap tetang status koleksi di perpustakaan, tanggal pengembalian, dan pemesanan item/koleksi<br />7)&nbsp;&nbsp;&nbsp; Detil informasi juga menampilkan gambar sampul buku, lampiran dalam format elektronik yang tersedia (jika ada) serta fasilitas menampilkan koleksi audio dan visual.<br />8)&nbsp;&nbsp;&nbsp; Menyediakan hyperlink tambahan untuk pencarian lanjutan berdasarkan penulis, dan subjek.<br /><br />Modul Sirkulasi (Circulation Module)<br />1)&nbsp;&nbsp;&nbsp; Mampu memproses peminjaman dan pengembalian koleksi secara efisien, efektif dan aman.<br />2)&nbsp;&nbsp;&nbsp; Mendukung fitur reservasi koleksi yang sedang dipinjam, termasuk reminder/pemberitahuan-nya.<br />3)&nbsp;&nbsp;&nbsp; Mendukung fitur manajemen denda. Dilengkapi fleksibilitas untuk pemakai membayar denda secara cicilan.<br />4)&nbsp;&nbsp;&nbsp; Mendukung fitur reminder untuk berbagai keperluan seperti melakukan black list terhadap pemakai yang bermasalah atau habis keanggotaannya.<br />5)&nbsp;&nbsp;&nbsp; Mendukung fitur pengkalenderan (calendaring) untuk diintegrasikan dengan penghitungan masa peminjaman, denda, dan lain-lain.<br />6)&nbsp;&nbsp;&nbsp; Memungkinkan penentuan hari-hari libur non-standar yang spesifik.<br />7)&nbsp;&nbsp;&nbsp; Dukungan terhadap ragam jenis tipe pemakai dengan masa pinjam beragam untuk berbagai jenis keanggotaan.<br />8)&nbsp;&nbsp;&nbsp; Menyimpan histori peminjaman anggota.<br />9)&nbsp;&nbsp;&nbsp; Mendukung pembuatan peraturan peminjaman yang sangat rinci dengan mengkombinasikan parameter keanggotaan, jenis koleksi, dan gmd selain aturan peminjaman standar berdasarkan jenis keanggotaan<br /><br />Modul Manajemen Keanggotaan (Membership Management Module)<br />1)&nbsp;&nbsp;&nbsp; Memungkinkan beragam tipe pemakai dengan ragam jenis kategori peminjaman, ragam jenis keanggotaan dan pembedaan setiap layanan sirkulasi dalam jumlah koleksi serta lama peminjaman untuk jenis koleksi untuk setiap jenis/kategori.<br />2)&nbsp;&nbsp;&nbsp; Dukungan terhadap input menggunakan barcode reader<br />3)&nbsp;&nbsp;&nbsp; Memungkinkan untuk menyimpan informasi preferensi pemakai atau subject interest.<br />4)&nbsp;&nbsp;&nbsp; Memungkinkan untuk menyimpan informasi tambahan untuk keperluan reminder pada saat transaksi.<br />5)&nbsp;&nbsp;&nbsp; Memungkinkan menyimpan informasi detail pemakai yang lebih lengkap.<br />6)&nbsp;&nbsp;&nbsp; Pencarian informasi anggota minimal berdasarkan nomor dan nama anggota.<br />7)&nbsp;&nbsp;&nbsp; Pembuatan kartu anggota yang dilengkapi dengan barcode untuk transaksi peminjaman.<br /><br />Modul Inventarisasi Koleksi (Stocktaking Module)<br />1)&nbsp;&nbsp;&nbsp; Proses inventarisasi koleksi bisa dilakukan secara bertahap dan parsial tanpa harus menutup layanan perpustakaan secara keseluruhan.<br />2)&nbsp;&nbsp;&nbsp; Proses inventarisasi bisa dilakukan secara efisien dan efektif.<br />3)&nbsp;&nbsp;&nbsp; Terdapat pilihan untuk menghapus data secara otomatis pada saat akhir proses inventarisasi terhadap koleksi yang dianggap hilang.<br /><br />Modul Statistik/Pelaporan (Report Module)<br />1)&nbsp;&nbsp;&nbsp; Meliputi pelaporan untuk semua modul-modul yang tersedia di Senayan.<br />2)&nbsp;&nbsp;&nbsp; Laporan Judul.<br />3)&nbsp;&nbsp;&nbsp; Laporan Items/Kopi koleksi.<br />4)&nbsp;&nbsp;&nbsp; Laporan Keanggotaan.<br />5)&nbsp;&nbsp;&nbsp; Laporan jumlah koleksi berdasarkan klasifikasi.<br />6)&nbsp;&nbsp;&nbsp; Laporan Keterlambatan.<br />7)&nbsp;&nbsp;&nbsp; Berbagai macam statistik seperti statistik koleksi, peminjaman, keanggotaan, keterpakaian koleksi.<br />8)&nbsp;&nbsp;&nbsp; Tampilan laporan yang sudah didesain printer-friendly, sehingga memudahkan untuk dicetak.<br />9)&nbsp;&nbsp;&nbsp; Filter data yang lengkap untuk setiap laporan.<br />10)&nbsp;&nbsp;&nbsp; API untuk pelaporan yang relatif mudah dipelajari untuk membuat custom report baru.<br /><br />Modul Manajemen Terbitan Berseri (Serial Control)<br />1)&nbsp;&nbsp;&nbsp; Manajemen data langganan.<br />2)&nbsp;&nbsp;&nbsp; Manajemen data Kardex.<br />3)&nbsp;&nbsp;&nbsp; Manajemen tracking data terbitan yang akan terbit dan yang sudah ada.<br />4)&nbsp;&nbsp;&nbsp; Memungkinkan tracking data terbitan berseri yang jadwal terbitnya tidak teratur (pengaturan yang fleksibel).<br /><br />Modul Lain-lain<br />1)&nbsp;&nbsp;&nbsp; Dukungan antar muka yang multi bahasa (internasionalisasi) dengan Gettext.<br />2)&nbsp;&nbsp;&nbsp; Dukungan terhadap penggunaan huruf bukan latin untuk pengisian data dan pencarian.<br /><br />Roadmap Pengembangan Senayan<br />SENAYAN akan terus dikembangkan oleh para pengembangnya beserta komunitas pengguna SENAYAN lainnya. Berikut adalah Roadmap pengembangan SENAYAN ke depannya:<br /><br />Pengembangan aplikasi:<br />1.&nbsp;&nbsp;&nbsp; Kompatibilitas dengan MARC dan standar pertukaran data yang komplit. Contoh implementasinya:<br />&bull;&nbsp;&nbsp;&nbsp; Memastikan bahwa format data bibliografi kompatibel dengan MARC secara lebih baik (minimal MARC light).<br />&bull;&nbsp;&nbsp;&nbsp; Dukungan terhadap RFID.<br />&bull;&nbsp;&nbsp;&nbsp; Fitur untuk impor / ekspor rekod dari The Online Computer Library Centre (OCLC), Research Libraries Information Network (RLIN), vendor sistem lain yang compliant dengan MARC.<br />&bull;&nbsp;&nbsp;&nbsp; Validasi data ISBN menggunakan modulus seven.<br />&bull;&nbsp;&nbsp;&nbsp; Dukungan terhadap standar di perpustakaan, seperti: Library of Congress Subject Headings, Library of Congress Classification, ALA filing rules, International Standard Bibliographic Description, ANSI Standard for Bibliographic Information Exchange on magnetic tape, Common communication format (ISO 2709).<br />2.&nbsp;&nbsp;&nbsp; Katalog induk/bersama (union catalog)<br />3.&nbsp;&nbsp;&nbsp; Implementasi Thesaurus. Contoh implementasinya:<br />&bull;&nbsp;&nbsp;&nbsp; Pemanfaatan tesaurus untuk proses pengatalogan.<br />&bull;&nbsp;&nbsp;&nbsp; Pemanfaatan tesaurus untuk proses pencarian, misalnya memberikan advis pencarian menggunakan knowledge base yang dibangun dengan sistem tesaurus.<br />4.&nbsp;&nbsp;&nbsp; Implementasi Library 2.0. Contoh implementasinya:<br />&bull;&nbsp;&nbsp;&nbsp; User bisa login dan mempunyai halaman personalisasi.<br />&bull;&nbsp;&nbsp;&nbsp; User bisa melakukan reservasi koleksi dan memperpanjang peminjaman.<br />&bull;&nbsp;&nbsp;&nbsp; User bisa melakukan komunikasi dengan pustakawan via messaging system.<br />&bull;&nbsp;&nbsp;&nbsp; User bisa melakukan tagging, rekomendasi koleksi dan menyimpannya didalam daftar koleksi favoritnya.<br />&bull;&nbsp;&nbsp;&nbsp; User bisa memberikan komentar terhadap koleksi.<br />&bull;&nbsp;&nbsp;&nbsp; Pustakawan bisa memasukkan preferensi pemakai didalam data keanggotaan. Preferensi tersebut bisa dimanfaatkan salah satunya untuk men-generate semacam daftar koleksi terpilih untuk dicetak atau ditampilkan ketika user login.<br />5.&nbsp;&nbsp;&nbsp; Peningkatan dukungan manajemen konten digital dan entri analitikal<br /><br />Pengembangan basis komunitas pengguna:<br />&bull;&nbsp;&nbsp;&nbsp; Membangun komunitas pengguna di berbagai kota <br />&bull;&nbsp;&nbsp;&nbsp; Mengadakan Senayan Developers Day untuk silaturahmi antar developer Senayan, update dokumentasi, penambahan fitur baru dan bug fixing dan mencari bibit pengembang yang baru.<br />&bull;&nbsp;&nbsp;&nbsp; Workshop/Seminar Nasional Tahunan<br />&bull;&nbsp;&nbsp;&nbsp; Jam Sessions rutin setiap 3 bulan</p>', 'about_slims', '2010-08-28 23:29:55', '2010-11-12 18:21:01', '1'),
(null, 'Modul yang Tersedia', '<p><!--intro_awal-->Sebagai sebuah Sistem Automasi Perpustakaan yang terintergrasi, modul-modul yang telah terdapat di SENAYAN antara lain: pengatalogan/bibliografi, keanggotaan, sirkulasi, masterfile, stock opname (inventarisasi koleksi), pelaporan/reporting, manajemen kontrol serial, digital library, dan lain-lain.<!--intro_akhir--></p>\r\n<p>Modul Pengatalogan (Cataloging Module)<br />1)&nbsp;&nbsp;&nbsp; Compliance dengan standar AACR2 (Anglo-American Cataloging Rules).<br />2)&nbsp;&nbsp;&nbsp; Fitur untuk membuat, mengedit, dan menghapus data bibliografi sesuai dengan standar deskripsi bibliografi AACR2 level ke dua.<br />3)&nbsp;&nbsp;&nbsp; Mendukung pengelolaan koleksi dalam berbagai macam format seperti monograph, terbitan berseri, audio visual, dsb.<br />4)&nbsp;&nbsp;&nbsp; Mendukung penyimpanan data bibliografi dari situs di Internet.<br />5)&nbsp;&nbsp;&nbsp; Mendukung penggunaan Barcode.<br />6)&nbsp;&nbsp;&nbsp; Manajemen item koleksi untuk dokumen dengan banyak kopi dan format yang berbeda.<br />7)&nbsp;&nbsp;&nbsp; Mendukung format XML untuk pertukaran data dengan menggunakan standar metadata MODS (Metadata Object Description Schema).<br />8)&nbsp;&nbsp;&nbsp; Pencetakan Barcode item/kopi koleksi Built-in.<br />9)&nbsp;&nbsp;&nbsp; Pencetakan Label Punggung koleksi Built-in.<br />10)&nbsp;&nbsp;&nbsp; Pengambilan data katalog melalui protokol Z3950 ke database koleksi Library of Congress.<br />11)&nbsp;&nbsp;&nbsp; Pengelolaan koleksi yang hilang, dalam perbaikan, dan rusak serta pencatatan statusnya untuk dilakukan pergantian/perbaikan terhadap koleksi.<br />12)&nbsp;&nbsp;&nbsp; Daftar kendali untuk pengarang (baik pengarang orang, badan/lembaga, dan pertemuan) sebagai standar konsistensi penuliasn<br />13)&nbsp;&nbsp;&nbsp; Pengaturan hak akses pengelolaan data bibliografi hanya untuk staf yang berhak.<br /><br />Modul Penelusuran (OPAC/Online Public Access catalog Module)<br />1)&nbsp;&nbsp;&nbsp; Pencarian sederhana.<br />2)&nbsp;&nbsp;&nbsp; Pencarian tingkat lanjut (Advanced).<br />3)&nbsp;&nbsp;&nbsp; Dukungan penggunaan Boolean''s Logic dan implementasi CQL (Common Query Language).<br />4)&nbsp;&nbsp;&nbsp; OPAC Web Services berbasis XML.<br />5)&nbsp;&nbsp;&nbsp; Mendukung akses OPAC melalui peralatan portabel (mobile device)<br />6)&nbsp;&nbsp;&nbsp; Menampilkan informasi lengkap tetang status koleksi di perpustakaan, tanggal pengembalian, dan pemesanan item/koleksi<br />7)&nbsp;&nbsp;&nbsp; Detil informasi juga menampilkan gambar sampul buku, lampiran dalam format elektronik yang tersedia (jika ada) serta fasilitas menampilkan koleksi audio dan visual.<br />8)&nbsp;&nbsp;&nbsp; Menyediakan hyperlink tambahan untuk pencarian lanjutan berdasarkan penulis, dan subjek.<br /><br />Modul Sirkulasi (Circulation Module)<br />1)&nbsp;&nbsp;&nbsp; Mampu memproses peminjaman dan pengembalian koleksi secara efisien, efektif dan aman.<br />2)&nbsp;&nbsp;&nbsp; Mendukung fitur reservasi koleksi yang sedang dipinjam, termasuk reminder/pemberitahuan-nya.<br />3)&nbsp;&nbsp;&nbsp; Mendukung fitur manajemen denda. Dilengkapi fleksibilitas untuk pemakai membayar denda secara cicilan.<br />4)&nbsp;&nbsp;&nbsp; Mendukung fitur reminder untuk berbagai keperluan seperti melakukan black list terhadap pemakai yang bermasalah atau habis keanggotaannya.<br />5)&nbsp;&nbsp;&nbsp; Mendukung fitur pengkalenderan (calendaring) untuk diintegrasikan dengan penghitungan masa peminjaman, denda, dan lain-lain.<br />6)&nbsp;&nbsp;&nbsp; Memungkinkan penentuan hari-hari libur non-standar yang spesifik.<br />7)&nbsp;&nbsp;&nbsp; Dukungan terhadap ragam jenis tipe pemakai dengan masa pinjam beragam untuk berbagai jenis keanggotaan.<br />8)&nbsp;&nbsp;&nbsp; Menyimpan histori peminjaman anggota.<br />9)&nbsp;&nbsp;&nbsp; Mendukung pembuatan peraturan peminjaman yang sangat rinci dengan mengkombinasikan parameter keanggotaan, jenis koleksi, dan gmd selain aturan peminjaman standar berdasarkan jenis keanggotaan<br /><br />Modul Manajemen Keanggotaan (Membership Management Module)<br />1)&nbsp;&nbsp;&nbsp; Memungkinkan beragam tipe pemakai dengan ragam jenis kategori peminjaman, ragam jenis keanggotaan dan pembedaan setiap layanan sirkulasi dalam jumlah koleksi serta lama peminjaman untuk jenis koleksi untuk setiap jenis/kategori.<br />2)&nbsp;&nbsp;&nbsp; Dukungan terhadap input menggunakan barcode reader<br />3)&nbsp;&nbsp;&nbsp; Memungkinkan untuk menyimpan informasi preferensi pemakai atau subject interest.<br />4)&nbsp;&nbsp;&nbsp; Memungkinkan untuk menyimpan informasi tambahan untuk keperluan reminder pada saat transaksi.<br />5)&nbsp;&nbsp;&nbsp; Memungkinkan menyimpan informasi detail pemakai yang lebih lengkap.<br />6)&nbsp;&nbsp;&nbsp; Pencarian informasi anggota minimal berdasarkan nomor dan nama anggota.<br />7)&nbsp;&nbsp;&nbsp; Pembuatan kartu anggota yang dilengkapi dengan barcode untuk transaksi peminjaman.<br /><br />Modul Inventarisasi Koleksi (Stocktaking Module)<br />1)&nbsp;&nbsp;&nbsp; Proses inventarisasi koleksi bisa dilakukan secara bertahap dan parsial tanpa harus menutup layanan perpustakaan secara keseluruhan.<br />2)&nbsp;&nbsp;&nbsp; Proses inventarisasi bisa dilakukan secara efisien dan efektif.<br />3)&nbsp;&nbsp;&nbsp; Terdapat pilihan untuk menghapus data secara otomatis pada saat akhir proses inventarisasi terhadap koleksi yang dianggap hilang.<br /><br />Modul Statistik/Pelaporan (Report Module)<br />1)&nbsp;&nbsp;&nbsp; Meliputi pelaporan untuk semua modul-modul yang tersedia di Senayan.<br />2)&nbsp;&nbsp;&nbsp; Laporan Judul.<br />3)&nbsp;&nbsp;&nbsp; Laporan Items/Kopi koleksi.<br />4)&nbsp;&nbsp;&nbsp; Laporan Keanggotaan.<br />5)&nbsp;&nbsp;&nbsp; Laporan jumlah koleksi berdasarkan klasifikasi.<br />6)&nbsp;&nbsp;&nbsp; Laporan Keterlambatan.<br />7)&nbsp;&nbsp;&nbsp; Berbagai macam statistik seperti statistik koleksi, peminjaman, keanggotaan, keterpakaian koleksi.<br />8)&nbsp;&nbsp;&nbsp; Tampilan laporan yang sudah didesain printer-friendly, sehingga memudahkan untuk dicetak.<br />9)&nbsp;&nbsp;&nbsp; Filter data yang lengkap untuk setiap laporan.<br />10)&nbsp;&nbsp;&nbsp; API untuk pelaporan yang relatif mudah dipelajari untuk membuat custom report baru.<br /><br />Modul Manajemen Terbitan Berseri (Serial Control)<br />1)&nbsp;&nbsp;&nbsp; Manajemen data langganan.<br />2)&nbsp;&nbsp;&nbsp; Manajemen data Kardex.<br />3)&nbsp;&nbsp;&nbsp; Manajemen tracking data terbitan yang akan terbit dan yang sudah ada.<br />4)&nbsp;&nbsp;&nbsp; Memungkinkan tracking data terbitan berseri yang jadwal terbitnya tidak teratur (pengaturan yang fleksibel).<br /><br />Modul Lain-lain<br />1)&nbsp;&nbsp;&nbsp; Dukungan antar muka yang multi bahasa (internasionalisasi) dengan Gettext.<br />2)&nbsp;&nbsp;&nbsp; Dukungan terhadap penggunaan huruf bukan latin untuk pengisian data dan pencarian.</p>', 'modul_tersedia', '2010-08-29 04:03:09', '2010-08-29 04:05:49', '1'),
(null, 'Lisensi SLiMS', '<p><!--intro_awal--><strong>SLiMS</strong> dilisensikan dibawah GNU/GPL (http://www.gnu.org/licenses/gpl.html) untuk menjamin kebebasan pengguna dalam menggunakannya. GNU General Public License (disingkat GNU GPL atau cukup GPL) merupakan suatu lisensi perangkat lunak bebas yang aslinya ditulis oleh Richard Stallman untuk proyek GNU. Lisensi GPL memberikan penerima salinan perangkat lunak hak dari perangkat lunak bebas dan menggunakan copyleft&nbsp; untuk memastikan kebebasan yang sama diterapkan pada versi berikutnya dari karya tersebut.<!--intro_akhir--></p>\r\n<p>&nbsp;GNU LESSER GENERAL PUBLIC LICENSE<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Version 3, 29 June 2007<br /><br />&nbsp;Copyright (C) 2007 Free Software Foundation, Inc. &lt;http://fsf.org/&gt;<br />&nbsp;Everyone is permitted to copy and distribute verbatim copies<br />&nbsp;of this license document, but changing it is not allowed.<br /><br /><br />&nbsp; This version of the GNU Lesser General Public License incorporates<br />the terms and conditions of version 3 of the GNU General Public<br />License, supplemented by the additional permissions listed below.<br /><br />&nbsp; 0. Additional Definitions.<br /><br />&nbsp; As used herein, \"this License\" refers to version 3 of the GNU Lesser<br />General Public License, and the \"GNU GPL\" refers to version 3 of the GNU<br />General Public License.<br /><br />&nbsp; \"The Library\" refers to a covered work governed by this License,<br />other than an Application or a Combined Work as defined below.<br /><br />&nbsp; An \"Application\" is any work that makes use of an interface provided<br />by the Library, but which is not otherwise based on the Library.<br />Defining a subclass of a class defined by the Library is deemed a mode<br />of using an interface provided by the Library.<br /><br />&nbsp; A \"Combined Work\" is a work produced by combining or linking an<br />Application with the Library.&nbsp; The particular version of the Library<br />with which the Combined Work was made is also called the \"Linked<br />Version\".<br /><br />&nbsp; The \"Minimal Corresponding Source\" for a Combined Work means the<br />Corresponding Source for the Combined Work, excluding any source code<br />for portions of the Combined Work that, considered in isolation, are<br />based on the Application, and not on the Linked Version.<br /><br />&nbsp; The \"Corresponding Application Code\" for a Combined Work means the<br />object code and/or source code for the Application, including any data<br />and utility programs needed for reproducing the Combined Work from the<br />Application, but excluding the System Libraries of the Combined Work.<br /><br />&nbsp; 1. Exception to Section 3 of the GNU GPL.<br /><br />&nbsp; You may convey a covered work under sections 3 and 4 of this License<br />without being bound by section 3 of the GNU GPL.<br /><br />&nbsp; 2. Conveying Modified Versions.<br /><br />&nbsp; If you modify a copy of the Library, and, in your modifications, a<br />facility refers to a function or data to be supplied by an Application<br />that uses the facility (other than as an argument passed when the<br />facility is invoked), then you may convey a copy of the modified<br />version:<br /><br />&nbsp;&nbsp; a) under this License, provided that you make a good faith effort to<br />&nbsp;&nbsp; ensure that, in the event an Application does not supply the<br />&nbsp;&nbsp; function or data, the facility still operates, and performs<br />&nbsp;&nbsp; whatever part of its purpose remains meaningful, or<br /><br />&nbsp;&nbsp; b) under the GNU GPL, with none of the additional permissions of<br />&nbsp;&nbsp; this License applicable to that copy.<br /><br />&nbsp; 3. Object Code Incorporating Material from Library Header Files.<br /><br />&nbsp; The object code form of an Application may incorporate material from<br />a header file that is part of the Library.&nbsp; You may convey such object<br />code under terms of your choice, provided that, if the incorporated<br />material is not limited to numerical parameters, data structure<br />layouts and accessors, or small macros, inline functions and templates<br />(ten or fewer lines in length), you do both of the following:<br /><br />&nbsp;&nbsp; a) Give prominent notice with each copy of the object code that the<br />&nbsp;&nbsp; Library is used in it and that the Library and its use are<br />&nbsp;&nbsp; covered by this License.<br /><br />&nbsp;&nbsp; b) Accompany the object code with a copy of the GNU GPL and this license<br />&nbsp;&nbsp; document.<br /><br />&nbsp; 4. Combined Works.<br /><br />&nbsp; You may convey a Combined Work under terms of your choice that,<br />taken together, effectively do not restrict modification of the<br />portions of the Library contained in the Combined Work and reverse<br />engineering for debugging such modifications, if you also do each of<br />the following:<br /><br />&nbsp;&nbsp; a) Give prominent notice with each copy of the Combined Work that<br />&nbsp;&nbsp; the Library is used in it and that the Library and its use are<br />&nbsp;&nbsp; covered by this License.<br /><br />&nbsp;&nbsp; b) Accompany the Combined Work with a copy of the GNU GPL and this license<br />&nbsp;&nbsp; document.<br /><br />&nbsp;&nbsp; c) For a Combined Work that displays copyright notices during<br />&nbsp;&nbsp; execution, include the copyright notice for the Library among<br />&nbsp;&nbsp; these notices, as well as a reference directing the user to the<br />&nbsp;&nbsp; copies of the GNU GPL and this license document.<br /><br />&nbsp;&nbsp; d) Do one of the following:<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 0) Convey the Minimal Corresponding Source under the terms of this<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; License, and the Corresponding Application Code in a form<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; suitable for, and under terms that permit, the user to<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; recombine or relink the Application with a modified version of<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; the Linked Version to produce a modified Combined Work, in the<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; manner specified by section 6 of the GNU GPL for conveying<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Corresponding Source.<br /><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 1) Use a suitable shared library mechanism for linking with the<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Library.&nbsp; A suitable mechanism is one that (a) uses at run time<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; a copy of the Library already present on the user''s computer<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; system, and (b) will operate properly with a modified version<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; of the Library that is interface-compatible with the Linked<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Version.<br /><br />&nbsp;&nbsp; e) Provide Installation Information, but only if you would otherwise<br />&nbsp;&nbsp; be required to provide such information under section 6 of the<br />&nbsp;&nbsp; GNU GPL, and only to the extent that such information is<br />&nbsp;&nbsp; necessary to install and execute a modified version of the<br />&nbsp;&nbsp; Combined Work produced by recombining or relinking the<br />&nbsp;&nbsp; Application with a modified version of the Linked Version. (If<br />&nbsp;&nbsp; you use option 4d0, the Installation Information must accompany<br />&nbsp;&nbsp; the Minimal Corresponding Source and Corresponding Application<br />&nbsp;&nbsp; Code. If you use option 4d1, you must provide the Installation<br />&nbsp;&nbsp; Information in the manner specified by section 6 of the GNU GPL<br />&nbsp;&nbsp; for conveying Corresponding Source.)<br /><br />&nbsp; 5. Combined Libraries.<br /><br />&nbsp; You may place library facilities that are a work based on the<br />Library side by side in a single library together with other library<br />facilities that are not Applications and are not covered by this<br />License, and convey such a combined library under terms of your<br />choice, if you do both of the following:<br /><br />&nbsp;&nbsp; a) Accompany the combined library with a copy of the same work based<br />&nbsp;&nbsp; on the Library, uncombined with any other library facilities,<br />&nbsp;&nbsp; conveyed under the terms of this License.<br /><br />&nbsp;&nbsp; b) Give prominent notice with the combined library that part of it<br />&nbsp;&nbsp; is a work based on the Library, and explaining where to find the<br />&nbsp;&nbsp; accompanying uncombined form of the same work.<br /><br />&nbsp; 6. Revised Versions of the GNU Lesser General Public License.<br /><br />&nbsp; The Free Software Foundation may publish revised and/or new versions<br />of the GNU Lesser General Public License from time to time. Such new<br />versions will be similar in spirit to the present version, but may<br />differ in detail to address new problems or concerns.<br /><br />&nbsp; Each version is given a distinguishing version number. If the<br />Library as you received it specifies that a certain numbered version<br />of the GNU Lesser General Public License \"or any later version\"<br />applies to it, you have the option of following the terms and<br />conditions either of that published version or of any later version<br />published by the Free Software Foundation. If the Library as you<br />received it does not specify a version number of the GNU Lesser<br />General Public License, you may choose any version of the GNU Lesser<br />General Public License ever published by the Free Software Foundation.<br /><br />&nbsp; If the Library as you received it specifies that a proxy can decide<br />whether future versions of the GNU Lesser General Public License shall<br />apply, that proxy''s public statement of acceptance of any version is<br />permanent authorization for you to choose that version for the<br />Library.</p>', 'lisensi_slims', '2010-08-29 04:04:33', '2010-11-12 22:15:43', '1');";

    $sql['insert'][] = "INSERT INTO `content` (`content_id`, `content_title`, `content_desc`, `content_path`, `input_date`, `last_update`, `content_ownpage`) VALUES
(null, 'Model Pengembangan Open Source', '<p><!--intro_awal-->Sumber terbuka (Inggris: open source) adalah sistem pengembangan yang tidak dikoordinasi oleh suatu individu / lembaga pusat, tetapi oleh para pelaku yang bekerja sama dengan memanfaatkan kode sumber (source-code) yang tersebar dan tersedia bebas (biasanya menggunakan fasilitas komunikasi internet). Pola pengembangan ini mengambil model ala bazaar, sehingga pola Open Source ini memiliki ciri bagi komunitasnya yaitu adanya dorongan yang bersumber dari budaya memberi.<!--intro_akhir--><br /><br />Pola Open Source lahir karena kebebasan berkarya, tanpa intervensi berpikir dan mengungkapkan apa yang diinginkan dengan menggunakan pengetahuan dan produk yang cocok. Kebebasan menjadi pertimbangan utama ketika dilepas ke publik. Komunitas yang lain mendapat kebebasan untuk belajar, mengutak-ngatik, merevisi ulang, membenarkan ataupun bahkan menyalahkan, tetapi kebebasan ini juga datang bersama dengan tanggung jawab, bukan bebas tanpa tanggung jawab.<br /><br />Pada intinya konsep sumber terbuka adalah membuka \"kode sumber\" dari sebuah perangkat lunak. Konsep ini terasa aneh pada awalnya dikarenakan kode sumber merupakan kunci dari sebuah perangkat lunak. Dengan diketahui logika yang ada di kode sumber, maka orang lain semestinya dapat membuat perangkat lunak yang sama fungsinya. Sumber terbuka hanya sebatas itu. Artinya, dia tidak harus gratis. Definisi sumber terbuka yang asli adalah seperti tertuang dalam OSD (Open Source Definition)/Definisi sumber terbuka.</p>\r\n<p>Pengembangan Senayan awalnya diinisiasi oleh pengelola Perpustakaan Depdiknas. Tetapi sekarang komunitas pengembang Senayan (Senayan Developer Community) yang lebih banyak mengambil peran dalam mengembangkan Senayan. Beberapa hal dibawah ini merupakan kultur yang dibangun dalam mengembangkan Senayan:<br />1.&nbsp;&nbsp;&nbsp; Meritokrasi. Siapa saja bisa berkontribusi. Mereka yang banyak memberikan kontribusi, akan mendapatkan privilege lebih dibandingkan yang lain.<br />2.&nbsp;&nbsp;&nbsp; Minimal punya concern terhadap pengembangan perpustakaan. Contoh lain: berlatar belakang pendidikan ilmu perpustakaan dan informasi, bekerja di perpustakaan, mengelola perpustakaan, dan lain-lain. Diharapkan dengan kondisi ini, sense of librarianship melekat di tiap developer/pengguna Senayan. Sejauh ini, semua developer senayan merupakan pustakawan atau berlatarbelakang pendidikan kepustakawanan (Information and Librarianship).<br />3.&nbsp;&nbsp;&nbsp; Release early, release often, and listen to your customer. Release early artinya setiap perbaikan dan penambahan fitur, secepat mungkin dirilis ke publik. Diharapkan bugs yang ada, bisa cepat ditemukan oleh komunitas, dilaporkan ke developer, untuk kemudian dirilis perbaikannya. Release often, artinya sesering mungkin memberikan update perbaikan bugs dan penambahan fitur. Ini &ldquo;memaksa&rdquo; developer Senayan untuk terus kreatif menambahkan fitur Senayan. Release often juga membuat pengguna berkeyakinan bahwa Senayan punya sustainability yang baik dan terus aktif dikembangkan. Selain itu, release often juga mempunyai dampak pemasaran. Pengguna dan calon pengguna, selalu diingatkan tentang keberadaan Senayan. Tentunya dengan cara yang elegan, yaitu rilis-rilis Senayan. Sejak dirilis ke publi pertama kali November 2007 sampai Juli 2009 (kurang lebih 20 bulan) telah dirilis 18 rilis resmi Senayan. Listen to your customer. Developer Senayan selalu berusaha mengakomodasi kebutuhan pengguna baik yang masuk melalui report di mailing list, ataupun melalui bugs tracking system. Tentu tidak semua masukan diakomodasi, harus disesuaikan dengan desain dan roadmap pengembangan Senayan.<br />4.&nbsp;&nbsp;&nbsp; Dokumentasi. Developer Senayan meyakini pentingnya dokumentasi yang baik dalam mensukseskan implementasi Senayan dibanyak tempat. Karena itu pengembang Senayan mempunyai tim khusus yang bertanggungjawab yang mengembangkan dokumentasi Senayan agar terus uo-to-date mengikuti rilis terbaru.<br />5.&nbsp;&nbsp;&nbsp; Agar ada percepatan dalam pengembangan dan untuk mengakrabkan antar pengembang Senayan, minimal setahun sekali diadakan Senayan Developers Day yang mengumpulkan para developer Senayan dari berbagai kota, dan melakukan coding bersama-sama.</p>', 'opensource', '2010-08-29 04:05:16', '2010-08-29 04:34:04', '1');";

    $sql['insert'][] = "INSERT INTO `mst_item_status` (`item_status_id`, `item_status_name`, `rules`, `input_date`, `last_update`, `no_loan`, `skip_stock_take`) VALUES
('MIS', 'Missing', NULL, DATE(NOW()), DATE(NOW()), '1', '1');";

    return $this->slims->query($sql, ['drop', 'create', 'alter', 'insert']);
  }

  /**
   * Upgrade role to v5.0.0
   *
   * @return mixed
   */
  function upgrade_role_14()
  {
    $sql['alter'][] = "ALTER TABLE  `mst_topic` ADD  `classification` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT  'Classification Code' AFTER  `auth_list` ;";
    $sql['alter'][] = "ALTER TABLE `biblio` ADD `sor` VARCHAR( 200 ) COLLATE utf8_unicode_ci NULL AFTER `title` ;";
    $sql['insert'][] = "INSERT INTO `setting` (`setting_id`, `setting_name`, `setting_value`) VALUES (NULL , 'ignore_holidays_fine_calc', 'b:0;');";

    return $this->slims->query($sql, ['alter', 'insert']);
  }

  /**
   * Upgrade role to v7.0.0
   *
   * @return mixed
   */
  function upgrade_role_15()
  {
    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `comment` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT,
  `biblio_id` int(11) NOT NULL,
  `member_id` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `input_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";

    $sql['insert'][] = "INSERT IGNORE INTO `setting` (`setting_id`, `setting_name`, `setting_value`)
VALUES (18, 'barcode_print_settings', 'a:12:{s:19:\"barcode_page_margin\";d:0.200000000000000011102230246251565404236316680908203125;s:21:\"barcode_items_per_row\";i:3;s:20:\"barcode_items_margin\";d:0.1000000000000000055511151231257827021181583404541015625;s:17:\"barcode_box_width\";i:7;s:18:\"barcode_box_height\";i:5;s:27:\"barcode_include_header_text\";i:1;s:17:\"barcode_cut_title\";i:50;s:19:\"barcode_header_text\";s:0:\"\";s:13:\"barcode_fonts\";s:41:\"Arial, Verdana, Helvetica, ''Trebuchet MS''\";s:17:\"barcode_font_size\";i:11;s:13:\"barcode_scale\";i:70;s:19:\"barcode_border_size\";i:1;}'),
(19, 'label_print_settings', 'a:10:{s:11:\"page_margin\";d:0.200000000000000011102230246251565404236316680908203125;s:13:\"items_per_row\";i:3;s:12:\"items_margin\";d:0.05000000000000000277555756156289135105907917022705078125;s:9:\"box_width\";i:8;s:10:\"box_height\";d:3.29999999999999982236431605997495353221893310546875;s:19:\"include_header_text\";i:1;s:11:\"header_text\";s:0:\"\";s:5:\"fonts\";s:41:\"Arial, Verdana, Helvetica, ''Trebuchet MS''\";s:9:\"font_size\";i:11;s:11:\"border_size\";i:1;}'),
(20, 'membercard_print_settings', 'a:1:{s:5:\"print\";a:1:{s:10:\"membercard\";a:61:{s:11:\"card_factor\";s:12:\"37.795275591\";s:21:\"card_include_id_label\";i:1;s:23:\"card_include_name_label\";i:1;s:22:\"card_include_pin_label\";i:1;s:23:\"card_include_inst_label\";i:0;s:24:\"card_include_email_label\";i:0;s:26:\"card_include_address_label\";i:1;s:26:\"card_include_barcode_label\";i:1;s:26:\"card_include_expired_label\";i:1;s:14:\"card_box_width\";d:8.5999999999999996447286321199499070644378662109375;s:15:\"card_box_height\";d:5.4000000000000003552713678800500929355621337890625;s:9:\"card_logo\";s:8:\"logo.png\";s:21:\"card_front_logo_width\";s:0:\"\";s:22:\"card_front_logo_height\";s:0:\"\";s:20:\"card_front_logo_left\";s:0:\"\";s:19:\"card_front_logo_top\";s:0:\"\";s:20:\"card_back_logo_width\";s:0:\"\";s:21:\"card_back_logo_height\";s:0:\"\";s:19:\"card_back_logo_left\";s:0:\"\";s:18:\"card_back_logo_top\";s:0:\"\";s:15:\"card_photo_left\";s:0:\"\";s:14:\"card_photo_top\";s:0:\"\";s:16:\"card_photo_width\";d:1.5;s:17:\"card_photo_height\";d:1.8000000000000000444089209850062616169452667236328125;s:23:\"card_front_header1_text\";s:19:\"Library Member Card\";s:28:\"card_front_header1_font_size\";s:2:\"12\";s:23:\"card_front_header2_text\";s:10:\"My Library\";s:28:\"card_front_header2_font_size\";s:2:\"12\";s:22:\"card_back_header1_text\";s:10:\"My Library\";s:27:\"card_back_header1_font_size\";s:2:\"12\";s:22:\"card_back_header2_text\";s:35:\"My Library Full Address and Website\";s:27:\"card_back_header2_font_size\";s:1:\"5\";s:17:\"card_header_color\";s:7:\"#0066FF\";s:18:\"card_bio_font_size\";s:2:\"11\";s:20:\"card_bio_font_weight\";s:4:\"bold\";s:20:\"card_bio_label_width\";s:3:\"100\";s:9:\"card_city\";s:9:\"City Name\";s:10:\"card_title\";s:15:\"Library Manager\";s:14:\"card_officials\";s:14:\"Librarian Name\";s:17:\"card_officials_id\";s:12:\"Librarian ID\";s:15:\"card_stamp_file\";s:9:\"stamp.png\";s:19:\"card_signature_file\";s:13:\"signature.png\";s:15:\"card_stamp_left\";s:0:\"\";s:14:\"card_stamp_top\";s:0:\"\";s:16:\"card_stamp_width\";s:0:\"\";s:17:\"card_stamp_height\";s:0:\"\";s:13:\"card_exp_left\";s:0:\"\";s:12:\"card_exp_top\";s:0:\"\";s:14:\"card_exp_width\";s:0:\"\";s:15:\"card_exp_height\";s:0:\"\";s:18:\"card_barcode_scale\";i:100;s:17:\"card_barcode_left\";s:0:\"\";s:16:\"card_barcode_top\";s:0:\"\";s:18:\"card_barcode_width\";s:0:\"\";s:19:\"card_barcode_height\";s:0:\"\";s:10:\"card_rules\";s:120:\"<ul><li>This card is published by Library.</li><li>Please return this card to its owner if you found it.</li></ul>\";s:20:\"card_rules_font_size\";s:1:\"8\";s:12:\"card_address\";s:76:\"My Library<br />website: http://slims.web.id, email : librarian@slims.web.id\";s:22:\"card_address_font_size\";s:1:\"7\";s:17:\"card_address_left\";s:0:\"\";s:16:\"card_address_top\";s:0:\"\";}}}');";

    $sql['update'][] = "UPDATE `setting` SET `setting_value`='a:2:{s:5:\"theme\";s:7:\"default\";s:3:\"css\";s:26:\"template/default/style.css\";}'
  WHERE `setting_name`='template';";

    $sql['update'][] = "UPDATE `setting` SET `setting_value`='a:2:{s:5:\"theme\";s:7:\"default\";s:3:\"css\";s:32:\"admin_template/default/style.css\";}'
  WHERE `setting_name`='admin_template';";

    $sql['update'][] = "UPDATE `content` SET `content_desc`='<div class=\"container admin_home\">\r\n<div class=\"row\">\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Bibliography</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon biblioIcon notAJAX\" href=\"index.php?mod=bibliography\"></a></div>\r\n<div class=\"col-sm-8\">The Bibliography module lets you manage your library bibliographical data. It also include collection items management to manage a copies of your library collection so it can be used in library circulation.</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Circulation</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon circulationIcon notAJAX\" href=\"index.php?mod=circulation\"></a></div>\r\n<div class=\"col-sm-8\">The Circulation module is used for doing library circulation transaction such as collection loans and return. In this module you can also create loan rules that will be used in loan transaction proccess.</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Membership</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon memberIcon notAJAX\" href=\"index.php?mod=membership\"></a></div>\r\n<div class=\"col-sm-8\">The Membership module lets you manage library members such adding, updating and also removing. You can also manage membership type in this module.</div>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"row\">\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Stock Take</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon stockTakeIcon notAJAX\" href=\"index.php?mod=stock_take\"></a></div>\r\n<div class=\"col-sm-8\">The Stock Take module is the easy way to do Stock Opname for your library collections. Follow several steps that ease your pain in Stock Opname proccess.</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Serial Control</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon serialIcon notAJAX\" href=\"index.php?mod=serial_control\"></a></div>\r\n<div class=\"col-sm-8\">Serial Control module help you manage library''s serial publication subscription. You can track issues for each subscription.</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Reporting</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon reportIcon notAJAX\" href=\"index.php?mod=reporting\"></a></div>\r\n<div class=\"col-sm-8\">Reporting lets you view various type of reports regardings membership data, circulation data and bibliographic data. All compiled on-the-fly from current library database.</div>\r\n</div>\r\n</div>\r\n</div>\r\n<div class=\"row\">\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>Master File</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon masterFileIcon notAJAX\" href=\"index.php?mod=master_file\"></a></div>\r\n<div class=\"col-sm-8\">The Master File modules lets you manage referential data that will be used by another modules. It include Authority File management such as Authority, Subject/Topic List, GMD and other data.</div>\r\n</div>\r\n</div>\r\n<div class=\"col-xs-6 col-md-4\">\r\n<h3>System</h3>\r\n<div class=\"row\">\r\n<div class=\"col-sm-2\"><a class=\"icon systemIcon notAJAX\" href=\"index.php?mod=system\"></a></div>\r\n<div class=\"col-sm-8\">The System module is used to configure application globally, manage index, manage librarian, and also backup database.</div>\r\n</div>\r\n</div>\r\n</div>\r\n</div>'
  WHERE `content_path`='adminhome';";

    $sql['alter'][] = "ALTER TABLE `user` ADD `email` VARCHAR( 200 ) NULL DEFAULT NULL AFTER `passwd` ,
ADD `user_type` SMALLINT( 2 ) NULL DEFAULT NULL AFTER `email` ,
ADD `user_image` VARCHAR( 250 ) NULL DEFAULT NULL AFTER `user_type` ,
ADD `social_media` TEXT NULL AFTER `user_image`;";

    return $this->slims->query($sql, ['create', 'alter', 'insert', 'update']);
  }

  /**
   * Upgrade role to v8.0.0
   *
   * @return mixed
   */
  function upgrade_role_16()
  {
    $sql['delete'][] = "DELETE FROM `setting` WHERE `setting`.`setting_name` = 'barcode_encoding';";
    $sql['update'][] = "UPDATE `setting` SET `setting_value` = 'a:2:{s:5:\"theme\";s:7:\"default\";s:3:\"css\";s:26:\"template/default/style.css\";}' WHERE `setting_id` = 3;";

    $sql['alter'][] = "ALTER TABLE `biblio` ADD `content_type_id` INT NULL DEFAULT NULL AFTER `spec_detail_info`,
  ADD `media_type_id` INT NULL DEFAULT NULL AFTER `content_type_id`,
  ADD `carrier_type_id` INT NULL DEFAULT NULL AFTER `media_type_id`,
  ADD INDEX (`content_type_id`, `media_type_id`, `carrier_type_id`) ;";

    $sql['alter'][] = "ALTER TABLE `search_biblio` ADD `content_type` VARCHAR(100) NULL DEFAULT NULL AFTER `image`, ADD `media_type` VARCHAR(100) NULL DEFAULT NULL AFTER `content_type`,
  ADD `carrier_type` VARCHAR(100) NULL DEFAULT NULL AFTER `media_type`, ADD INDEX (`content_type`, `media_type`, `carrier_type`);";

    $sql['alter'][] = "ALTER TABLE `user` CHANGE `passwd` `passwd` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;";
    $sql['alter'][] = "ALTER TABLE `member` CHANGE `mpasswd` `mpasswd` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;";

    /*--
    -- Table structure for table `mst_carrier_type`
    --*/

    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `mst_carrier_type` (
`id` int(11) NOT NULL,
  `carrier_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `code2` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    /*--
    -- Dumping data for table `mst_carrier_type`
    --*/

    $sql['insert'][] = "INSERT INTO `mst_carrier_type` (`id`, `carrier_type`, `code`, `code2`, `input_date`, `last_update`) VALUES
(1, 'audio cartridge', 'sg', 'g', now(), now()),
(2, 'audio cylinder', 'se', 'e', now(), now()),
(3, 'audio disc', 'sd', 'd', now(), now()),
(4, 'sound track reel', 'si', 'i', now(), now()),
(5, 'audio roll', 'sq', 'q', now(), now()),
(6, 'audiocassette', 'ss', 's', now(), now()),
(7, 'audiotape reel', 'st', 't', now(), now()),
(8, 'other (audio)', 'sz', 'z', now(), now()),
(9, 'computer card', 'ck', 'k', now(), now()),
(10, 'computer chip cartridge', 'cb', 'b', now(), now()),
(11, 'computer disc', 'cd', 'd', now(), now()),
(12, 'computer disc cartridge', 'ce', 'e', now(), now()),
(13, 'computer tape cartridge', 'ca', 'a', now(), now()),
(14, 'computer tape cassette', 'cf', 'f', now(), now()),
(15, 'computer tape reel', 'ch', 'h', now(), now()),
(16, 'online resource', 'cr', 'r', now(), now()),
(17, 'other (computer)', 'cz', 'z', now(), now()),
(18, 'aperture card', 'ha', 'a', now(), now()),
(19, 'microfiche', 'he', 'e', now(), now()),
(20, 'microfiche cassette', 'hf', 'f', now(), now()),
(21, 'microfilm cartridge', 'hb', 'b', now(), now()),
(22, 'microfilm cassette', 'hc', 'c', now(), now()),
(23, 'microfilm reel', 'hd', 'd', now(), now()),
(24, 'microfilm roll', 'hj', 'j', now(), now()),
(25, 'microfilm slip', 'hh', 'h', now(), now()),
(26, 'microopaque', 'hg', 'g', now(), now()),
(27, 'other (microform)', 'hz', 'z', now(), now()),
(28, 'microscope slide', 'pp', 'p', now(), now()),
(29, 'other (microscope)', 'pz', 'z', now(), now()),
(30, 'film cartridge', 'mc', 'c', now(), now()),
(31, 'film cassette', 'mf', 'f', now(), now()),
(32, 'film reel', 'mr', 'r', now(), now()),
(33, 'film roll', 'mo', 'o', now(), now()),
(34, 'filmslip', 'gd', 'd', now(), now()),
(35, 'filmstrip', 'gf', 'f', now(), now()),
(36, 'filmstrip cartridge', 'gc', 'c', now(), now()),
(37, 'overhead transparency', 'gt', 't', now(), now()),
(38, 'slide', 'gs', 's', now(), now()),
(39, 'other (projected image)', 'mz', 'z', now(), now()),
(40, 'stereograph card', 'eh', 'h', now(), now()),
(41, 'stereograph disc', 'es', 's', now(), now()),
(42, 'other (stereographic)', 'ez', 'z', now(), now()),
(43, 'card', 'no', 'o', now(), now()),
(44, 'flipchart', 'nn', 'n', now(), now()),
(45, 'roll', 'na', 'a', now(), now()),
(46, 'sheet', 'nb', 'b', now(), now()),
(47, 'volume', 'nc', 'c', now(), now()),
(48, 'object', 'nr', 'r', now(), now()),
(49, 'other (unmediated)', 'nz', '', now(), now()),
(50, 'video cartridge', 'vc', '', now(), now()),
(51, 'videocassette', 'vf', '', now(), now()),
(52, 'videodisc', 'vd', '', now(), now()),
(53, 'videotape reel', 'vr', '', now(), now()),
(54, 'other (video)', 'vz', '', now(), now()),
(55, 'unspecified', 'zu', 'u', now(), now());";

    /*--
    -- Table structure for table `mst_content_type`
    --*/

    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `mst_content_type` (
`id` int(11) NOT NULL,
  `content_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `code2` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    /*--
    -- Dumping data for table `mst_content_type`
    --*/

    $sql['insert'][] = "INSERT INTO `mst_content_type` (`id`, `content_type`, `code`, `code2`, `input_date`, `last_update`) VALUES
(1, 'cartographic dataset', 'crd', 'e', now(), now()),
(2, 'cartographic image', 'cri', 'e', now(), now()),
(3, 'cartographic moving image', 'crm', 'e', now(), now()),
(4, 'cartographic tactile image', 'crt', 'e', now(), now()),
(5, 'cartographic tactile three-dimensional form', 'crn', 'e', now(), now()),
(6, 'cartographic three-dimensional form', 'crf', 'e', now(), now()),
(7, 'computer dataset', 'cod', 'm', now(), now()),
(8, 'computer program', 'cop', 'm', now(), now()),
(9, 'notated movement', 'ntv', 'a', now(), now()),
(10, 'notated music', 'ntm', 'c', now(), now()),
(11, 'performed music', 'prm', 'j', now(), now()),
(12, 'sounds', 'snd', 'i', now(), now()),
(13, 'spoken word', 'spw', 'i', now(), now()),
(14, 'still image', 'sti', 'k', now(), now()),
(15, 'tactile image', 'tci', 'k', now(), now()),
(16, 'tactile notated music', 'tcm', 'c', now(), now()),
(17, 'tactile notated movement', 'tcn', 'a', now(), now()),
(18, 'tactile text', 'tct', 'a', now(), now()),
(19, 'tactile three-dimensional form', 'tcf', 'r', now(), now()),
(20, 'text', 'txt', 'a', now(), now()),
(21, 'three-dimensional form', 'tdf', 'r', now(), now()),
(22, 'three-dimensional moving image', 'tdm', 'g', now(), now()),
(23, 'two-dimensional moving image', 'tdi', 'g', now(), now()),
(24, 'other', 'xxx', 'o', now(), now()),
(25, 'unspecified', 'zzz', ' ', now(), now());";

    /*--
    -- Table structure for table `mst_media_type`
    --*/

    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `mst_media_type` (
`id` int(11) NOT NULL,
  `media_type` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `code` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `code2` char(1) COLLATE utf8_unicode_ci NOT NULL,
  `input_date` datetime NOT NULL,
  `last_update` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    /*--
    -- Dumping data for table `mst_media_type`
    --*/

    $sql['insert'][] = "INSERT INTO `mst_media_type` (`id`, `media_type`, `code`, `code2`, `input_date`, `last_update`) VALUES
(1, 'audio', 's', 's', now(), now()),
(2, 'computer', 'c', 'c', now(), now()),
(3, 'microform', 'h', 'h', now(), now()),
(4, 'microscopic', 'p', ' ', now(), now()),
(5, 'projected', 'g', 'g', now(), now()),
(6, 'stereographic', 'e', ' ', now(), now()),
(7, 'unmediated', 'n', 't', now(), now()),
(8, 'video', 'v', 'v', now(), now()),
(9, 'other', 'x', 'z', now(), now()),
(10, 'unspecified', 'z', 'z', now(), now());";

    /*--
    -- Table structure for table `mst_relation_term`
    --*/

    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `mst_relation_term` (
`ID` int(11) NOT NULL,
  `rt_id` varchar(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `rt_desc` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;";

    /*--
    -- Dumping data for table `mst_relation_term`
    --*/

    $sql['insert'][] = "INSERT INTO `mst_relation_term` (`ID`, `rt_id`, `rt_desc`) VALUES
(1, 'U', 'Use'),
(2, 'UF', 'Use For'),
(3, 'BT', 'Broader Term'),
(4, 'NT', 'Narrower Term'),
(5, 'RT', 'Related Term'),
(6, 'SA', 'See Also');";


    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `biblio_relation` (
  `biblio_id` int(11) NOT NULL DEFAULT '0',
  `rel_biblio_id` int(11) NOT NULL DEFAULT '0',
  `rel_type` int(1) DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    /*--
    -- Indexes for dumped tables
    --

    --
    -- Indexes for table `biblio_relation`
    --*/
    $sql['alter'][] = "ALTER TABLE `biblio_relation`
 ADD PRIMARY KEY (`biblio_id`,`rel_biblio_id`);";

    /*--
    -- Table structure for table `mst_voc_ctrl`
    --*/

    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `mst_voc_ctrl` (
  `topic_id` int(11) NOT NULL,
`vocabolary_id` int(11) NOT NULL,
  `rt_id` varchar(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `related_topic_id` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=43 ;";


    $sql['insert'][] = "INSERT IGNORE INTO `setting` (`setting_name`, `setting_value`) VALUES
('shortcuts_1', 'a:6:{i:0;s:42:\"Daftar Bibliografi|/bibliography/index.php\";i:1;s:61:\"Tambah Bibliografi Baru|/bibliography/index.php?action=detail\";i:2;s:51:\"Mulai Transaksi|/circulation/index.php?action=start\";i:3;s:48:\"Pengembalian Kilat|/circulation/quick_return.php\";i:4;s:42:\"Lihat Daftar Anggota|/membership/index.php\";i:5;s:37:\"Shortcut Setting|/system/shortcut.php\";}');";

    /*--
    -- Indexes for table `mst_relation_term`
    --*/
    $sql['alter'][] = "ALTER TABLE `mst_relation_term`
 ADD PRIMARY KEY (`ID`);";

    /*--
    -- Indexes for table `mst_voc_ctrl`
    --*/
    $sql['alter'][] = "ALTER TABLE `mst_voc_ctrl`
 ADD PRIMARY KEY (`vocabolary_id`);";

    /*--
    -- AUTO_INCREMENT for table `mst_relation_term`
    --*/
    $sql['alter'][] = "ALTER TABLE `mst_relation_term`
MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=7;";

    /*--
    -- AUTO_INCREMENT for table `mst_voc_ctrl`
    --*/
    $sql['alter'][] = "ALTER TABLE `mst_voc_ctrl`
MODIFY `vocabolary_id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;";

    $sql['alter'][] = "ALTER TABLE `mst_carrier_type`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `media_type` (`carrier_type`), ADD KEY `code` (`code`);";

    $sql['alter'][] = "ALTER TABLE `mst_content_type`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `content_type` (`content_type`), ADD KEY `code` (`code`);";

    $sql['alter'][] = "ALTER TABLE `mst_media_type`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `media_type` (`media_type`), ADD KEY `code` (`code`);";

    $sql['alter'][] = "ALTER TABLE `mst_carrier_type`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=56;";

    $sql['alter'][] = "ALTER TABLE `mst_content_type`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=26;";

    $sql['alter'][] = "ALTER TABLE `mst_media_type`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=11;";

    $sql['alter'][] = "ALTER TABLE `content` ADD `is_news` SMALLINT(1) NULL DEFAULT NULL AFTER `content_path`;";

    return $this->slims->query($sql, ['create', 'alter', 'delete', 'insert', 'update']);
  }

  /**
   * Upgrade role to v8.2.0
   *
   * @return mixed
   */
  function upgrade_role_17()
  {

    $sql['insert'][] = "INSERT IGNORE INTO `setting` (`setting_name`, `setting_value`) VALUES
('enable_visitor_limitation', 's:1:\"0\";'),
('time_visitor_limitation', 's:2:\"60\";');";

    $sql['alter'][] = "ALTER TABLE `mst_voc_ctrl` CHANGE `vocabolary_id` `vocabolary_id` INT(11) NOT NULL AUTO_INCREMENT;";

    return $this->slims->query($sql, ['alter', 'insert']);
  }

  /**
   * Upgrade role to v8.3.0
   *
   * @return mixed
   */
  function upgrade_role_18()
  {
    $sql['alter'][] = "ALTER TABLE  `biblio` ADD  `uid` INT( 11 ) NULL DEFAULT NULL ,
ADD INDEX (  `uid` ) ;";

    $sql['alter'][] = "ALTER TABLE  `item` ADD  `uid` INT( 11 ) NULL DEFAULT NULL ,
ADD INDEX (  `uid` ) ;";

    $sql['create'][] = "CREATE TABLE `mst_servers` (
  `server_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `uri` text COLLATE utf8_unicode_ci NOT NULL, 
  `server_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 - p2p server; 2 - z3950; 3 - z3950  SRU',
  `input_date` datetime NOT NULL,
  `last_update` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $sql['alter'][] = "ALTER TABLE `mst_servers` ADD PRIMARY KEY (`server_id`);";

    $sql['alter'][] = "ALTER TABLE `mst_servers` MODIFY `server_id` int(11) NOT NULL AUTO_INCREMENT;";

    $sql['alter'][] = "ALTER TABLE `mst_voc_ctrl` ADD `scope` TEXT NULL DEFAULT NULL; ";

    return $this->slims->query($sql, ['create', 'alter']);
  }

  /**
   * Upgrade role to v8.3.1
   *
   * @return mixed
   */
  function upgrade_role_19()
  {
    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `biblio_log` (
  `biblio_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `biblio_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `realname` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `affectedrow` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rawdata` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `additional_information` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`biblio_log_id`),
  KEY `realname` (`realname`),
  KEY `biblio_id` (`biblio_id`),
  KEY `user_id` (`user_id`),
  KEY `ip` (`ip`),
  KEY `action` (`action`),
  KEY `affectedrow` (`affectedrow`),
  KEY `date` (`date`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `rawdata` (`rawdata`),
  FULLTEXT KEY `additional_information` (`additional_information`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;";

    $sql['alter'][] = "ALTER TABLE  `loan` ADD  `input_date` DATETIME NULL DEFAULT NULL ,
ADD  `last_update` DATETIME NULL DEFAULT NULL ,
ADD  `uid` INT( 11 ) NULL DEFAULT NULL ,
ADD INDEX (  `input_date` ,  `last_update` ,  `uid` ) ;";

    return $this->slims->query($sql, ['create', 'alter']);
  }

  /**
   * Upgrade role to v9.0.0
   *
   * @return array
   */
  function upgrade_role_20()
  {
    $sql['create'][] = "CREATE TABLE IF NOT EXISTS `loan_history` (
  `loan_id` int(11) NOT NULL,
  `item_code` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `biblio_id` int(11) NOT NULL,
  `title` varchar(300) COLLATE utf8_unicode_ci DEFAULT NULL,
  `call_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `classification` varchar(40) COLLATE utf8_unicode_ci  DEFAULT NULL,
  `gmd_name` varchar(30) COLLATE utf8_unicode_ci  DEFAULT NULL,
  `language_name` varchar(20) COLLATE utf8_unicode_ci  DEFAULT NULL,
  `location_name` varchar(100) COLLATE utf8_unicode_ci  DEFAULT NULL,
  `collection_type_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `member_id` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `member_name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `member_type_name` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `loan_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `renewed` int(11) NOT NULL DEFAULT '0',
  `is_lent` int(11) NOT NULL DEFAULT '0',
  `is_return` int(11) NOT NULL DEFAULT '0',
  `return_date` date DEFAULT NULL,
  `input_date` datetime DEFAULT NULL,
  `last_update` datetime DEFAULT NULL,
   PRIMARY KEY (`loan_id`),
   KEY `member_name` (`member_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    $sql['insert'][] = "
        INSERT IGNORE INTO `loan_history` 
      (`loan_id`, 
        `item_code`, 
        `biblio_id`,
        `member_id`, 
        `loan_date`, 
        `due_date`, 
        `renewed`, 
        `is_lent`, 
        `is_return`, 
        `return_date`,
        `input_date`,
        `last_update`,
        `title`,
        `call_number`,
        `classification`,
        `gmd_name`,
        `language_name`,
        `location_name`,
        `collection_type_name`,
        `member_name`,
        `member_type_name`
        )
    (SELECT l.loan_id,
        l.item_code,
        b.biblio_id,
        l.member_id,
        l.loan_date,
        l.due_date,
        l.renewed,
        l.is_lent,
        l.is_return,
        l.return_date,
        IF(day(l.input_date) IS NULL,NULL, l.input_date),
        IF(day(l.last_update) IS NULL,NULL, l.last_update),
        b.title,
        IF(i.call_number IS NULL,b.call_number,i.call_number),
        b.classification,
        g.gmd_name,
        ml.language_name,
        mlc.location_name,
        mct.coll_type_name,
        m.member_name,
        mmt.member_type_name 
    FROM loan l LEFT JOIN item i ON i.item_code=l.item_code
    LEFT JOIN biblio b ON b.biblio_id=i.biblio_id
    LEFT JOIN mst_gmd g ON g.gmd_id=b.gmd_id
    LEFT JOIN mst_language ml ON ml.language_id=b.language_id 
    LEFT JOIN mst_location mlc ON mlc.location_id=i.location_id
    LEFT JOIN member m ON m.member_id=l.member_id
    LEFT JOIN mst_coll_type mct ON mct.coll_type_id=i.coll_type_id
    LEFT JOIN mst_member_type mmt ON mmt.member_type_id=m.member_type_id WHERE m.member_id IS NOT NULL AND b.biblio_id IS NOT NULL);";

    $error = $this->slims->query($sql, ['create', 'insert']);

    // create trigger
    $query_trigger[] = "
    DROP TRIGGER IF EXISTS `delete_loan_history`;";
    $query_trigger[] = "
    CREATE TRIGGER `delete_loan_history` AFTER DELETE ON `loan`
     FOR EACH ROW DELETE FROM `loan_history` WHERE loan_id=OLD.loan_id;";

    $query_trigger[] = "
    DROP TRIGGER IF EXISTS `update_loan_history`;";
    $query_trigger[] = "
    CREATE TRIGGER `update_loan_history` AFTER UPDATE ON `loan`
     FOR EACH ROW UPDATE loan_history 
    SET is_lent=NEW.is_lent,
    is_return=NEW.is_return,
    renewed=NEW.renewed,
    return_date=NEW.return_date
    WHERE loan_id=NEW.loan_id;";

    $query_trigger[] = "
    DROP TRIGGER IF EXISTS `insert_loan_history`;";

    $query_trigger[] = "
    CREATE TRIGGER `insert_loan_history` AFTER INSERT ON `loan`
     FOR EACH ROW INSERT INTO loan_history
     SET loan_id=NEW.loan_id,
     item_code=NEW.item_code,
     member_id=NEW.member_id,
     loan_date=NEW.loan_date,
     due_date=NEW.due_date,
     renewed=NEW.renewed,
     is_lent=NEW.is_lent,
     is_return=NEW.is_return,
     return_date=NEW.return_date,
     input_date=NEW.input_date,
     last_update=NEW.last_update,
     title=(SELECT b.title FROM biblio b LEFT JOIN item i ON i.biblio_id=b.biblio_id WHERE i.item_code=NEW.item_code),
     biblio_id=(SELECT b.biblio_id FROM biblio b LEFT JOIN item i ON i.biblio_id=b.biblio_id WHERE i.item_code=NEW.item_code),
     call_number=(SELECT IF(i.call_number IS NULL, b.call_number,i.call_number) FROM biblio b LEFT JOIN item i ON i.biblio_id=b.biblio_id WHERE i.item_code=NEW.item_code),
     classification=(SELECT b.classification FROM biblio b LEFT JOIN item i ON i.biblio_id=b.biblio_id WHERE i.item_code=NEW.item_code),
     gmd_name=(SELECT g.gmd_name FROM biblio b LEFT JOIN item i ON i.biblio_id=b.biblio_id LEFT JOIN mst_gmd g ON g.gmd_id=b.gmd_id WHERE i.item_code=NEW.item_code),
     language_name=(SELECT l.language_name FROM biblio b LEFT JOIN item i ON i.biblio_id=b.biblio_id LEFT JOIN mst_language l ON b.language_id=l.language_id WHERE i.item_code=NEW.item_code),
     location_name=(SELECT ml.location_name FROM item i LEFT JOIN mst_location ml ON i.location_id=ml.location_id WHERE i.item_code=NEW.item_code),
     collection_type_name=(SELECT mct.coll_type_name FROM mst_coll_type mct LEFT JOIN item i ON i.coll_type_id=mct.coll_type_id WHERE i.item_code=NEW.item_code),
     member_name=(SELECT m.member_name FROM member m WHERE m.member_id=NEW.member_id),
     member_type_name=(SELECT mmt.member_type_name FROM mst_member_type mmt LEFT JOIN member m ON m.member_type_id=mmt.member_type_id WHERE m.member_id=NEW.member_id);";

    $error_trigger = $this->slims->queryTrigger($query_trigger);
    $error = array_merge($error, $error_trigger);

    // fix mst_topic:classification
    $fix_classification = $this->slims->changeColumn('mst_topic', [
      'field' => 'classification',
      'type' => 'varchar(50)',
      'null' => true,
      'default' => null
    ]);

    if ($fix_classification) $error[] = $fix_classification;

    return $error;
  }

  /**
   * Upgrade role to v9.1.0
   */
  function upgrade_role_21()
  {
  }

  /**
   * Upgrade role to v9.1.1
   */
  function upgrade_role_22()
  {
  }

  /**
   * Upgrade role to v9.2.0
   */
  function upgrade_role_23()
  {
    $sql['alter'][] = "ALTER TABLE `user` ADD `forgot` VARCHAR(80) COLLATE 'utf8_unicode_ci' DEFAULT NULL AFTER `groups`;";

    $sql['alter'][] = "ALTER TABLE `user` ADD `admin_template` text COLLATE 'utf8_unicode_ci' DEFAULT NULL AFTER `forgot`;";

    $sql['alter'][] = "ALTER TABLE `backup_log` CHANGE `backup_time` `backup_time` datetime NULL AFTER `user_id`;";

    $sql['alter'][] = "ALTER TABLE `user` CHANGE `input_date` `input_date` date NULL AFTER `groups`;";

    $sql['alter'][] = "ALTER TABLE `stock_take_item` CHANGE `checked_by` `checked_by` varchar(50) COLLATE 'utf8_unicode_ci' NULL AFTER `status`;";

    $sql['alter'][] = "ALTER TABLE `biblio_author` ADD INDEX `biblio_id` (`biblio_id`), ADD INDEX `author_id` (`author_id`);";

    $sql['alter'][] = "ALTER TABLE `biblio_topic` ADD INDEX `biblio_id` (`biblio_id`), ADD INDEX `topic_id` (`topic_id`);";

    $sql['insert'][] = "INSERT INTO `setting` (`setting_name`, `setting_value`) VALUES ('logo_image', NULL);";

    $sql['create'][] = "
    CREATE TABLE IF NOT EXISTS `mst_custom_field` (
      `field_id` int(11) NOT NULL AUTO_INCREMENT,
      `primary_table` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
      `dbfield` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
      `label` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
      `type` enum('text','checklist','numeric','dropdown','longtext','choice','date') COLLATE utf8_unicode_ci NOT NULL,
      `default` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
      `max` int(11) DEFAULT NULL,
      `data` text COLLATE utf8_unicode_ci,
      `indexed` tinyint(1) DEFAULT NULL,
      `class` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
      `is_public` tinyint(1) DEFAULT NULL,
      `width` int(5) DEFAULT '100',
      `note` text COLLATE utf8_unicode_ci,
      PRIMARY KEY (`dbfield`),
      UNIQUE KEY `field_id` (`field_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    $sql['create'][] = "
    CREATE TABLE IF NOT EXISTS `files_read` (
      `filelog_id` int(11) NOT NULL AUTO_INCREMENT,
      `file_id` int(11) NOT NULL,
      `date_read` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `member_id` int(11) DEFAULT NULL,
      `user_id` int(11) DEFAULT NULL,
      `client_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
      PRIMARY KEY (`filelog_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    $error = $this->slims->query($sql, ['alter','insert','create']);

    return $error;
  }

  /**
   * Upgrade role to v9.2.1
   */
  function upgrade_role_24()
  {
  }

  /**
   * Upgrade role to v9.2.2
   */
  function upgrade_role_25()
  {
  }

  /**
   * Upgrade role to v9.3.0
   */
  function upgrade_role_26()
  {
      $sql['create'][] = "
        CREATE TABLE IF NOT EXISTS `plugins` (
          `id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
          `path` text COLLATE utf8mb4_unicode_ci NOT NULL,
          `created_at` datetime NOT NULL,
          `uid` int(11) NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

      $sql['alter'][] = "ALTER TABLE `group_access` ADD `menus` json NULL AFTER `module_id`;";

      $sql['alter'][] = "ALTER TABLE `biblio_attachment` ADD `placement` enum('link','popup','embed') COLLATE 'utf8_unicode_ci' NULL AFTER `file_id`;";

      $sql['alter'][] = "ALTER TABLE `system_log` ADD `sub_module` varchar(50) COLLATE 'utf8_unicode_ci' NULL AFTER `log_location`, ADD `action` varchar(50) COLLATE 'utf8_unicode_ci' NULL AFTER `sub_module`;";

      $sql['alter'][] = "ALTER TABLE `files_read` CHANGE `member_id` `member_id` varchar(20) NULL AFTER `date_read`;";

      $sql['alter'][] = "ALTER TABLE `backup_log` CHANGE `backup_file` `backup_file` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;";

      return $this->slims->query($sql, ['create', 'alter']);
  }

    /**
     * Upgrade role to v9.3.1
     */
    function upgrade_role_27()
    {
    }

    /**
     * Upgrade role to v9.4.0
     */
    function upgrade_role_28()
    {
        $sql['alter'][] = "ALTER TABLE `plugins`
                                ADD `options` json NULL AFTER `path`,
                                ADD `updated_at` datetime NULL AFTER `created_at`,
                                ADD `deleted_at` datetime NULL AFTER `updated_at`;";
        $sql['alter'][] = "ALTER TABLE `plugins` ADD UNIQUE `id` (`id`);";

        return $this->slims->query($sql, ['alter']);
    }

    /**
     * Upgrade role to v9.4.1
     */
    function upgrade_role_29()
    {
    }

    /**
     * Upgrade role to v9.4.2
     */
    function upgrade_role_30()
    {
    }

    /**
     * Upgrade role to v9.5.0
     */
    function upgrade_role_31()
    {
        $sql['alter'][] = "ALTER TABLE `files` ADD `file_key` text COLLATE 'utf8_unicode_ci' NULL AFTER `file_desc`;";
        if($_POST['oldVersion']??0 > 19) {
          $sql['alter'][] = "ALTER TABLE `biblio` DROP `update_date`;";
        }
        $sql['alter'][] = "ALTER TABLE `mst_topic` CHANGE `classification` `classification` varchar(50) COLLATE 'utf8_unicode_ci' NULL COMMENT 'Classification Code' AFTER `auth_list`;";
        $sql['alter'][] = "ALTER TABLE `content` ADD `is_draft` smallint(1) NULL DEFAULT '0' AFTER `is_news`, ADD `publish_date` date NULL AFTER `is_draft`;";

        $sql['create'][] = "CREATE TABLE IF NOT EXISTS `index_words` (
          `id` bigint NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `word` varchar(50) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
          `num_hits` int NOT NULL,
          `doc_hits` int NOT NULL
        ) ENGINE='MyISAM' COLLATE 'utf8mb4_unicode_ci';";

        $sql['create'][] = "CREATE TABLE IF NOT EXISTS `index_documents` (
          `document_id` int(11) NOT NULL,
          `word_id` bigint(20) NOT NULL,
          `location` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
          `hit_count` int(11) NOT NULL,
          PRIMARY KEY (`document_id`,`word_id`,`location`),
          KEY `document_id` (`document_id`),
          KEY `word_id` (`word_id`),
          KEY `location` (`location`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        return $this->slims->query($sql, ['create', 'alter']);
    }

    /**
     * Upgrade role to v9.5.2
     */
    function upgrade_role_32()
    {
        $sql['alter'][] = 'ALTER TABLE `search_biblio` DROP INDEX `title`, ADD FULLTEXT `title` (`title`, `series_title`)';
        $sql['create'][] = "CREATE TABLE IF NOT EXISTS `biblio_mark` (
          `id` varchar(32) NOT NULL,
          `member_id` varchar(20) NOT NULL,
          `biblio_id` int(11) NOT NULL,
          `created_at` datetime NOT NULL DEFAULT current_timestamp(),
          UNIQUE KEY `id` (`id`),
          KEY `member_id_idx` (`member_id`),
          KEY `biblio_id_idx` (`biblio_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        return $this->slims->query($sql, ['create', 'alter']);
    }

    /**
     * Upgrade role to v9.6.0
     */
    function upgrade_role_33()
    {
        $sql['alter'][] = "ALTER TABLE `user` ADD `2fa` text COLLATE 'utf8_unicode_ci' NULL AFTER `passwd`;";
        $sql['create'][] = "CREATE TABLE IF NOT EXISTS `mst_visitor_room` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `name` varchar(50) NOT NULL,
          `unique_code` varchar(5) NOT NULL COMMENT 'Code for identification each room',
          `created_at` datetime DEFAULT NULL,
          `updated_at`datetime NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_code_unq` (`unique_code`),
          KEY `unique_code_idx` (`unique_code`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $sql['alter'][] = "ALTER TABLE `visitor_count` ADD `room_code` varchar(5) COLLATE 'utf8_unicode_ci' NULL AFTER `institution`;";
        $sql['alter'][] = "ALTER TABLE `visitor_count` ADD INDEX `room_code` (`room_code`);";
        $sql['create'][] = "CREATE TABLE IF NOT EXISTS `cache` (
          `name` varchar(64) NOT NULL,
          `contents` text NOT NULL,
          `created_at` datetime NOT NULL,
          `updated_at` datetime NOT NULL,
          `expired_at` datetime DEFAULT NULL,
          UNIQUE KEY `name` (`name`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $sql['update'][] = "UPDATE `mst_item_status` SET `skip_stock_take` = 1 WHERE `item_status_id` IN ('NL','R')";

        return $this->slims->query($sql, ['create', 'alter','update']);
    }

    /**
     * Upgrade role to v9.6.0
     */
    function upgrade_role_34(){}
}