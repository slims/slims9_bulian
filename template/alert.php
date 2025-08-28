<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-09-12 21:31:47
 * @modify date 2022-09-12 21:35:48
 * @license GPLv3
 * @desc [description]
 */

defined('INDEX_AUTH') or die('Direct access is not allowed!');
?>
<div class="alert <?= simbio_security::xssFree($alertType) ?>" role="alert">
  <h4 class="alert-heading font-weight-bold"><?= simbio_security::xssFree($alertTitle) ?></h4>
  <p><?= strip_tags($alertMessage, $sysconf['content']['allowable_tags']) ?></p>
</div>