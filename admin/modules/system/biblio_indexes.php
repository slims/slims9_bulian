<?php
/**
 * Copyright (C) 2010  Wardiyono (wynerst@gmail.com), Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

/* Biblio Index Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require MDLBS.'system/biblio_indexer.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
  die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}

if ($sysconf['index']['type'] == 'mongodb') {
  if (!class_exists('MongoClient')) {
    throw new Exception('PHP Mongodb extension library is not installed yet!');
  } else {
	  $Mongo = new MongoClient();
		// select index
		$biblio = $Mongo->slims->biblio;
	}
}

/* main content */
if (isset($_POST['detail']) OR (isset($_GET['action']) AND $_GET['action'] == 'detail')) {
  if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
  }

  /* empty table */
  if ($_GET['detail'] == 'empty') {
    $indexer = new biblio_indexer($dbs);
    $empty = $indexer->emptyingIndex();
    if ($empty) {
      $message = __('Index table truncated!');
    } else {
      $message = __('Index table FAILED to truncated, probably because of database query error!');
    }
    $_SESSION['message'] = $message;
  }

  /* Update table */
  if ($_GET['detail'] == 'update') {
    set_time_limit(0);
    $indexer = new biblio_indexer($dbs);
    $indexer->updateFullIndex();
    $finish_minutes = $indexer->indexing_time/60;
    $finish_sec = $indexer->indexing_time%60;
    // message
    $message = sprintf(__('<strong>%d</strong> records (from total of <strong>%d</strong>) re-indexed. Finished in %d minutes %d second(s)'), $indexer->indexed, $indexer->total_records, $finish_minutes, $finish_sec);
    if ($indexer->failed) {
      $message = '<div style="color: #f00;">'.sprintf(__('<strong>%d</strong> index records failed to indexed. The IDs are: %s'), count($indexer->failed), implode(', ', $indexer->failed)).'</div>';
    }
    $_SESSION['message'] = $message;
  }

  /* re-create index table */
  if ($_GET['detail'] == 'reindex') {
    set_time_limit(0);
    $bib_sql = 'SELECT COUNT(*) FROM search_biblio';
    $rec_bib_q = $dbs->query($bib_sql);
    $rec_bib_d = $rec_bib_q->fetch_row();
    if ($rec_bib_d[0] > 0) {
    	$message = __('Please empty the Index first before re-creating the Index');
    	echo '<div class="errorBox">'.$message.'</div>'."\n";
    } else {
    	$indexer = new biblio_indexer($dbs);
    	$indexer->createFullIndex(false);
    	$finish_minutes = $indexer->indexing_time/60;
    	$finish_sec = $indexer->indexing_time%60;
    	// message
    	$message = sprintf(__('<strong>%d</strong> records (from total of <strong>%d</strong>) re-indexed. Finished in %d second(s)'), $indexer->indexed, $indexer->total_records, $finish_minutes, $finish_sec);
    	if ($indexer->failed) {
    	  $message = '<div style="color: #f00;">'.sprintf(__('<strong>%d</strong> index records failed to indexed. The IDs are: %s'), count($indexer->failed), implode(', ', $indexer->failed)).'</div>';
    	}
    	$_SESSION['message'] = $message;
    }
  }
  
  echo '<script type="text/javascript">parent.$(\'#mainContent\').simbioAJAX(\''.$_SERVER['PHP_SELF'].'\');</script>';
  exit();
} else {
?>
<fieldset class="menuBox">
<div class="menuBoxInner systemIcon">
  <div class="per_title">
  <h2><?php echo __('Bibliographic Index'); ?></h2>
  </div>
      <div class="sub_section">.
        <div class="btn-group">
          <a href="<?php echo MWB; ?>system/biblio_indexes.php?action=detail&detail=empty" class="btn btn-default" style="color: red"><i class="glyphicon glyphicon-trash"></i>&nbsp;<?php echo __('Emptying Index'); ?></a>
          <a href="<?php echo MWB; ?>system/biblio_indexes.php?action=detail&detail=reindex" class="btn btn-default"><i class="glyphicon glyphicon-refresh"></i>&nbsp;<?php echo __('Re-create Index'); ?></a>
          <a href="<?php echo MWB; ?>system/biblio_indexes.php?action=detail&detail=update" class="btn btn-default"><i class="glyphicon glyphicon-plus"></i>&nbsp;<?php echo __('Update Index'); ?></a>
        </div>
      </div>
      <div class="infoBox">Bibliographic Index will speed up catalog search</div>
</div>
</fieldset>
<?php
echo '<div class="infoBox">'."\n";
// Index info
$rec_bib_q = $dbs->query('SELECT COUNT(*) FROM biblio');
$rec_bib_d = $rec_bib_q->fetch_row();
$bib_total = $rec_bib_d[0];
if ($sysconf['index']['type'] == 'mongodb' && isset($biblio)) {
  $idx_total = $biblio->count();
} else {
  $idx_bib_q = $dbs->query('SELECT COUNT(*) FROM search_biblio');
  $idx_bib_d = $idx_bib_q->fetch_row();
  $idx_total = $idx_bib_d[0];
}
$unidx_total = $bib_total - $idx_total;

if (isset($_SESSION['message'])) {
  echo '<div class="alert alert-info">'.$_SESSION['message'].'</div>';
  unset($_SESSION['message']);
}
echo '<div>Total data on biblio: ' . $bib_total . ' records.</div>';
echo '<div>Total indexed data: ' . $idx_total . ' records.</div>';
echo '<div>Unidexed data: ' . $unidx_total . ' records.</div>';
echo '</div>';
}
