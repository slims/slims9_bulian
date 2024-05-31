#!/Applications/XAMPP/xamppfiles/bin/php
<?php
/**
 * Copyright (C) 2010  Wardiyono (wynerst@gmail.com), Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

/* Biblio Index Command Line Updater */
define('INDEX_AUTH', '1');
// main system configuration
require 'sysconfig.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require MDLBS.'system/biblio_indexer.inc.php';

if ($argc < 2) {
    echo "Usage: \n";
    echo $argv[0]." <option>\n";
    echo "Where <option> can be one of: (reindex|empty|update)\n\n";
    echo 'Index Information: '."\n";
    // Index info
    $rec_bib_q = $dbs->query('SELECT COUNT(*) FROM biblio');
    $rec_bib_d = $rec_bib_q->fetch_row();
    $bib_total = $rec_bib_d[0];
    $idx_bib_q = $dbs->query('SELECT COUNT(*) FROM search_biblio');
    $idx_bib_d = $idx_bib_q->fetch_row();
    $idx_total = $idx_bib_d[0];
    $unidx_total = $bib_total - $idx_total;

    echo 'Total data on biblio: ' . $bib_total . ' records.'."\n";
    echo 'Total indexed data: ' . $idx_total . ' records.'."\n";
    echo 'Unidexed data: ' . $unidx_total . ' records.'."\n";
    echo "\n";
    exit(0);
}

$is_verbose = in_array('--verbose', $argv) || in_array('-v', $argv);

/* empty table */
if ($argv[1] == 'empty') {
	$indexer = new biblio_indexer($dbs);
	$empty = $indexer->emptyingIndex();
	if ($empty) {
		$message = 'Index table truncated!';
	} else {
		$message = 'Index table FAILED to truncated, probably because of database query error!';
	}
	echo $message."\n";
	exit(0);
}

/* Update table */
if ($argv[1] == 'update') {
	set_time_limit(0);
    echo "Updating index. Please wait...\n";
	$indexer = new biblio_indexer($dbs, $is_verbose);
	$indexer->updateFullIndex();
	// message
    $finish_minutes = $indexer->indexing_time/60;
    $finish_sec = $indexer->indexing_time%60;
	$message = sprintf('%d records (from total of %d) re-indexed. Finished in %d minutes %d second(s)', $indexer->indexed, $indexer->total_records, $finish_minutes, $finish_sec);
	if ($indexer->failed) {
		$message = 	sprintf('%d index records failed to indexed. The IDs are: %s', count($indexer->failed), implode(', ', $indexer->failed));
	}
	echo $message."\n";
    exit(0);
}

/* re-create index table */
if ($argv[1] == 'reindex') {
	set_time_limit(0);
    echo "Recreating index table. Please wait...\n";
	$bib_sql = 'SELECT COUNT(*) FROM search_biblio';
	$rec_bib_q = $dbs->query($bib_sql);
	$rec_bib_d = $rec_bib_q->fetch_row();
	if ($rec_bib_d[0] > 0) {
		$message = 'Please empty the Index first before re-creating the Index';
		echo $message."\n";
        exit(6);
	} else {
		$indexer = new biblio_indexer($dbs, $is_verbose);
		$indexer->createFullIndex(false);
		// message
        $finish_minutes = $indexer->indexing_time/60;
        $finish_sec = $indexer->indexing_time%60;
		$message = sprintf('%d records (from total of %d) re-indexed. Finished in %d minutes %d second(s)', $indexer->indexed, $indexer->total_records, $finish_minutes, $finish_sec);
		if ($indexer->failed) {
			$message = 	sprintf('%d index records failed to indexed. The IDs are: %s', count($indexer->failed), implode(', ', $indexer->failed));
		}
		echo $message."\n";
        exit(0);
	}
}
