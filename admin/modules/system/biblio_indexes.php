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
    throw new Exception(__('PHP Mongodb extension library is not installed yet!'));
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
      utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' emptied index table', 'Index', 'Delete');
      $message = [
        'content' => __('Index table truncated!'),
        'type' => 'alert-success'
      ];
    } else {
      $message = [
        'content' => __('Index table FAILED to truncated, probably because of database query error!'),
        'type' => 'alert-danger'
      ];
    }
    $_SESSION['message'] = $message;
    redirect()->simbioAJAX($_SERVER['PHP_SELF']);
  }

  /* Update table */
  if ($_GET['detail'] == 'update') {
    set_time_limit(0);
    $indexer = new biblio_indexer($dbs);
    $indexer->updateFullIndex();
    $finish_minutes = $indexer->indexing_time/60;
    $finish_sec = $indexer->indexing_time%60;
    // message
    $message = [
      'content' => sprintf(__('<strong>%d</strong> records (from total of <strong>%d</strong>) re-indexed. Finished in %d minutes %d second(s)'), $indexer->indexed, $indexer->total_records, $finish_minutes, $finish_sec),
      'type' => 'alert-success'
    ];
    $_log = sprintf('%d of %d records re-indexed', $indexer->indexed,$indexer->total_records);
    if ($indexer->failed) {
      $message = [
        'content' => '<div class="font-danger">'.sprintf(__('<strong>%d</strong> index records failed to indexed. The IDs are: %s'), count($indexer->failed), implode(', ', $indexer->failed)).'</div>',
        'type' => 'alert-danger'
      ];
      $_log .=  sprintf(' with $d failed', count($indexer->failed));
    }
    utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' update index table ('.$_log.')', 'Index', 'Update');
    $_SESSION['message'] = $message;
    redirect()->simbioAJAX($_SERVER['PHP_SELF']);
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
    	$indexer = new biblio_indexer($dbs, bool_verbose: true);
      $indexer->createFullIndex(false);
    	$finish_minutes = $indexer->indexing_time/60;
    	$finish_sec = ((int)$indexer->indexing_time)%60;
    	// message
    	$message = [
        'content' => sprintf(__('<strong>%d</strong> records (from total of <strong>%d</strong>) re-indexed. Finished in %d second(s)'), $indexer->indexed, $indexer->total_records, $finish_minutes, $finish_sec),
        'type' => 'alert-success'
      ];
    	if ($indexer->failed) {
    	  $message = [
          'content' => '<div class="text-danger">'.sprintf(__('<strong>%d</strong> index records failed to indexed. The IDs are: %s'), count($indexer->failed), implode(', ', $indexer->failed)).'</div>',
          'type' => 'alert-danger'
        ];
    	}
      utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'System', $_SESSION['realname'].' execute re-index table' , 'Index', 'Re-create');
    	$_SESSION['message'] = $message;
    }

    if (!$indexer->failed) redirect()->simbioAJAX($_SERVER['PHP_SELF']);
  }
  exit();
} else {
?>
<div class="menuBox">
<div class="menuBoxInner systemIcon">
  <div class="per_title">
  <h2><?php echo __('Bibliographic Index'); ?></h2>
  </div>
      <div class="sub_section">.
        <div class="btn-group">
          <a href="<?php echo MWB; ?>system/biblio_indexes.php?action=detail&detail=empty" class="btn btn-danger" > <?php echo __('Emptying Index'); ?></a>
          <a target="progress" href="<?php echo MWB; ?>system/biblio_indexes.php?action=detail&detail=reindex" class="btn btn-default"><?php echo __('Re-create Index'); ?></a>
          <a target="progress" href="<?php echo MWB; ?>system/biblio_indexes.php?action=detail&detail=update" class="btn btn-default"><?php echo __('Update Index'); ?></a>
        </div>
      </div>
      <div class="infoBox"><?php echo __('Bibliographic indexing will speed up on cataloging search') ?></div>
</div>
</div>
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
  echo '<div class="alert ' . $_SESSION['message']['type'] . '">'.$_SESSION['message']['content'].'</div>';
  unset($_SESSION['message']);
}
echo '<div>'.__('Total data on biblio: ') . '<strong>' . $bib_total . '</strong>' . __(' records.').'</div>';
echo '<div>'.__('Total indexed data: ') . '<strong id="indexed">' . $idx_total . '</strong>' . __(' records.').'</div>';
echo '<div>'.__('Unidexed data: ') . '<strong id="unindexed">' .  $unidx_total . '</strong>' . __(' records.').'</div>';
echo '</div>';
}
?>
<iframe name="progress" class="w-full <?= isDev() ? 'd-block' : 'd-none'  ?>" style="height: 300px"></iframe>
