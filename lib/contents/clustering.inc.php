<?php
/**
 * Copyright (C) 2012  Arie Nugraha (dicarve@yahoo.com)
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

// Check if search clustering enabled
if (!$sysconf['enable_search_clustering']) { exit(); }
// only run on "index" type index
if ($sysconf['index']['type'] != 'index') { exit(); }

if (!isset($_GET['q'])) {
  echo "No Cluster Found!";
  exit();
} else {
  sleep(3);
  $cluster_limit = 30;
  $cluster_num_show = 5;

  $q = trim(strip_tags($_GET['q']));

  require SIMBIO.'simbio_UTILS/simbio_tokenizecql.inc.php';
  require LIB.'biblio_list_model.inc.php';
  // index choice
  if ($sysconf['index']['type'] == 'index') {
    require LIB.'biblio_list_index.inc.php';
  } else if ($sysconf['index']['type'] == 'sphinx' && file_exists(LIB.'sphinx/sphinxapi.php')) {
    require LIB.'sphinx/sphinxapi.php';
    require LIB.'biblio_list_sphinx.inc.php';
    $sysconf['opac_result_num'] = (int)$sysconf['opac_result_num'];
  }

  // create biblio list object
  try {
    $biblio_list = new biblio_list($dbs, $sysconf['opac_result_num']);
  } catch (Exception $err) {
    die($err->getMessage());
  }

  $sql_criteria = $biblio_list->setSQLcriteria($q);

  // cluster by GMD
  $gmd_cluster_q = $dbs->query('SELECT TRIM(gmd) AS `Cluster Name`, COUNT(biblio_id) AS `Cluster Count` FROM search_biblio AS `index` '
    .( $sql_criteria['sql_criteria']?' WHERE '.$sql_criteria['sql_criteria']:'' ).' GROUP BY `Cluster Name` LIMIT '.$cluster_limit);
  if ($gmd_cluster_q->num_rows > 0) {
    echo '<h3 class="cluster-title">'.__('GMD').'</h3>'."\n";
    echo '<ul class="cluster-list">'."\n";
    $i = 0;
    while ($cluster_data = $gmd_cluster_q->fetch_assoc()) {
      if (trim($cluster_data['Cluster Name']) == '') continue;
      $hidden = '';
      if ($i > $cluster_num_show-1) {
        $hidden = ' cluster-hidden';  
      }
      echo '<li class="cluster-item'.$hidden.'"><a href="index.php?keywords='.urlencode('('.$q.') AND gmd="'.$cluster_data['Cluster Name'].'"').'&search=Search&fromcluster=1">'.$cluster_data['Cluster Name'].' <span class="cluster-item-count">'.$cluster_data['Cluster Count'].'</span></a></li>'."\n";
      $i++;
    }
    echo '</ul>'."\n";
  }

  // cluster by Collection type
  $coll_type_cluster_q = $dbs->query('SELECT TRIM(collection_types) AS `Cluster Name`, COUNT(biblio_id) AS `Cluster Count` FROM search_biblio AS `index` '
    .( $sql_criteria['sql_criteria']?' WHERE '.$sql_criteria['sql_criteria']:'' ).' GROUP BY `Cluster Name` LIMIT '.$cluster_limit);
  if ($coll_type_cluster_q->num_rows > 0) {
    echo '<h3 class="cluster-title">'.__('Collection Type').'</h3>'."\n";
    echo '<ul class="cluster-list">'."\n";
    $i = 0;
    while ($cluster_data = $coll_type_cluster_q->fetch_assoc()) {
      if (trim($cluster_data['Cluster Name']) == '') continue;
      $hidden = '';
      if ($i > $cluster_num_show-1) {
        $hidden = ' cluster-hidden';  
      }
      echo '<li class="cluster-item'.$hidden.'"><a href="index.php?keywords='.urlencode('('.$q.') AND colltype="'.$cluster_data['Cluster Name'].'"').'&search=Search&fromcluster=1">'.$cluster_data['Cluster Name'].' <span class="cluster-item-count">'.$cluster_data['Cluster Count'].'</span></a></li>'."\n";
      $i++;
    }
    echo '</ul>'."\n";
  }

  // cluster by subject
  $subj_cluster_q = $dbs->query('SELECT TRIM(topic) AS `Cluster Name`, COUNT(biblio_id) AS `Cluster Count` FROM search_biblio AS `index` '
    .( $sql_criteria['sql_criteria']?' WHERE '.$sql_criteria['sql_criteria']:'' ).' GROUP BY `Cluster Name` LIMIT '.$cluster_limit);
  if ($subj_cluster_q->num_rows > 0) {
    echo '<h3 class="cluster-title">'.__('Subject(s)').'</h3>'."\n";
    echo '<ul class="cluster-list">'."\n";
    $i = 0;
    while ($cluster_data = $subj_cluster_q->fetch_assoc()) {
      if (trim($cluster_data['Cluster Name']) == '') continue;
      $hidden = '';
      if ($i > $cluster_num_show-1) {
        $hidden = ' cluster-hidden';  
      }
      echo '<li class="cluster-item'.$hidden.'"><a href="index.php?keywords='.urlencode('('.$q.') AND subject="'.$cluster_data['Cluster Name'].'"').'&search=Search&fromcluster=1">'.$cluster_data['Cluster Name'].' <span class="cluster-item-count">'.$cluster_data['Cluster Count'].'</span></a></li>'."\n";
      $i++;
    }
    if ($i > $cluster_num_show) {
      echo '<li class="cluster-item"><a href="#" class="cluster-more">'.__('More result').'...</a></li>';
    }
    echo '</ul>'."\n";
  }

  // cluster by author
  $auth_cluster_q = $dbs->query('SELECT TRIM(author) AS `Cluster Name`, COUNT(biblio_id) AS `Cluster Count` FROM search_biblio AS `index` '
    .( $sql_criteria['sql_criteria']?' WHERE '.$sql_criteria['sql_criteria']:'' ).' GROUP BY `Cluster Name` LIMIT '.$cluster_limit);
  if ($auth_cluster_q->num_rows > 0) {
    echo '<h3 class="cluster-title">'.__('Author(s)').'</h3>'."\n";
    echo '<ul class="cluster-list">'."\n";
    $i = 0;
    while ($cluster_data = $auth_cluster_q->fetch_assoc()) {
      if (trim($cluster_data['Cluster Name']) == '') continue;
      $hidden = '';
      if ($i > $cluster_num_show-1) {
        $hidden = ' cluster-hidden';  
      }
      echo '<li class="cluster-item'.$hidden.'"><a href="index.php?keywords='.urlencode('('.$q.') AND author="'.$cluster_data['Cluster Name'].'"').'&search=Search&fromcluster=1">'.$cluster_data['Cluster Name'].' <span class="cluster-item-count">'.$cluster_data['Cluster Count'].'</a></a></li>'."\n";
      $i++;
    }
    if ($i > $cluster_num_show) {
      echo '<li class="cluster-item"><a href="#" class="cluster-more">'.__('More result').'...</a></li>';
    }
    echo '</ul>'."\n";
  }
}
?>
<script type="text/javascript">
$(document).ready( function() {
 $('.cluster-more').toggle( function(evt) {
  evt.preventDefault();
  $(this).parents('.cluster-list').find('.cluster-hidden').show();
 }, function(evt) {
  evt.preventDefault();
  $(this).parents('.cluster-list').find('.cluster-hidden').hide();
 })
})
</script>
<?php

exit();