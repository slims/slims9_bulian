<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 2019-01-29 22:16
 * @File name           : login_template.inc.php
 */

if (isset($_GET['p']) && $_GET['p'] === 'visitor') {
  include "classic.php";
  include "parts/header.php";
  echo $main_content;
  include __DIR__ . "/assets/js/vegas.js.php";
  echo '</body></html>';
} else {
  include "index_template.inc.php";
}