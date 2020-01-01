<?php
/**
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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

/* Serial Control Management section */


// key to authenticate
define('INDEX_AUTH', '1');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB.'admin/default/session.inc.php';
}

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-serialcontrol');

require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_FILE/simbio_file_upload.inc.php';

// privileges checking
$can_read = utility::havePrivilege('serial_control', 'r');
$can_write = utility::havePrivilege('serial_control', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

/* search form */
?>
<div class="menuBox">
<div class="menuBoxInner serialIcon">
	<div class="per_title">
	    <h2><?php echo __('Serial Control'); ?></h2>
  </div>
	<div class="sub_section">
    <form name="search" action="<?php echo MWB; ?>serial_control/index.php" id="search" method="get" class="form-inline"><?php echo __('Search'); ?> 
    <input type="text" name="keywords" id="keywords" class="form-control col-3" />
    <select name="field" class="form-control col-2">
        <option value="0"><?php echo __('ALL'); ?></option>
        <option value="title"><?php echo __('Title'); ?></option>
        <option value="topic"><?php echo __('Subject(s)'); ?></option>
        <option value="author_name"><?php echo __('Author(s)'); ?></option>
        <option value="isbn_issn"><?php echo __('ISBN/ISSN'); ?></option>
    </select>
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
    </form>
  </div>
</div>
</div>
<script type="text/javascript">
// focus text field
$('keywords').focus();
</script>
<?php
/* search form end */

/* main content */
/* SERIAL SUBSCRIPTION LIST */
// callback function
$count = 1;
function subscriptionDetail($obj_db, $array_data)
{
    global $can_read, $can_write, $count;
    $_output = '<span class="pr-2"><a class="font-weight-bold notAJAX openPopUp" width="780" height="500" title="' . __('Edit Bibliographic data') . '" href="'.MWB.'bibliography/pop_biblio.php?action=detail&inPopUp=true&itemID='.$array_data[0].'&itemCollID=0">'.$array_data[1].'</a> - <em>('.$array_data[2].')</em></span>';
    if ($can_read AND $can_write) {
        $_output .= '<a href="#" class="s-btn btn btn-default btn-sm notAJAX" onclick="javascript: $(\'#subscriptionListCont'.$count.'\').show(); setIframeContent(\'subscriptionList'.$count.'\', \''.MWB.'serial_control/subscription.php?biblioID='.$array_data[0].'&detail=true\');" title="'.__('Add New Subscription').'">'.__('Add New Subscription').'</a>';
    }
    $_output .= '<a href="#" class="s-btn btn btn-success btn-sm notAJAX" onclick="$(\'#subscriptionListCont'.$count.'\').show(); setIframeContent(\'subscriptionList'.$count.'\', \''.MWB.'serial_control/subscription.php?biblioID='.$array_data[0].'\');" title="'.__('View Subscriptions').'">'.__('View Subscriptions').'</a> ';
    $_output .= '<div id="subscriptionListCont'.$count.'" style="display: none;">';
    $_output .= '<a href="#" class="btn btn-danger notAJAX" title="'.__('Close').'" onclick="$(\'#subscriptionListCont'.$count.'\').hide()">'.__('Close').'</a>';
    $_output .= '<iframe id="subscriptionList'.$count.'" src="'.MWB.'serial_control/subscription.php?biblioID='.$array_data[0].'" style="width: 100%; height: 270px;"></iframe>';
    $_output .= '</div>';
    $count++;
    return $_output;
}
// create datagrid
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn('b.biblio_id', 'b.title AS \''.__('Serial Title').'\'',
    'fr.frequency AS \'Frequency\'');
$datagrid->invisible_fields = array(0, 2);
$datagrid->modifyColumnContent(1, 'callback{subscriptionDetail}');
$datagrid->setSQLorder('b.last_update DESC');
// table alias and field relation
$tables['bsub'] = array('title', 'isbn_issn');
$tables['mt'] = array('topic');
if (isset($_GET['field']) AND !empty($_GET['field'])) {
    foreach ($tables as $table_alias=>$fields) {
        if (!in_array($_GET['field'], $fields)) {
            // remove unneeded array
            unset($tables[$table_alias]);
        }
    }
    // check if fields array is empty to prevent SQL error
    if (!$tables) {
        $tables['bsub'] = array('title', 'isbn_issn');
        $tables['mt'] = array('topic');
    }
}
// set default criteria
$criteria = 'bsub.frequency_id>0';
// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keyword = utility::filterData('keywords', 'get', true, true, true);
    $words = explode(' ', $keyword);
    if (count($words) > 1) {
        $concat_sql = ' (';
        foreach ($words as $word) {
            $concat_sql .= '(';
            foreach ($tables as $table_alias => $fields) {
                foreach ($fields as $field) {
                    $concat_sql .= $table_alias.'.'.$field." LIKE '%$word%' OR ";
                }
            }
            // remove the last OR
            $concat_sql = substr_replace($concat_sql, '', -4);
            $concat_sql .= ') AND';
        }
        // remove the last AND
        $concat_sql = substr_replace($concat_sql, '', -3);
        $concat_sql .= ') ';
        $criteria = $concat_sql;
    } else {
        $concat_sql = '';
        foreach ($tables as $table_alias => $fields) {
            foreach ($fields as $field) {
                $concat_sql .= $table_alias.'.'.$field." LIKE '%$keyword%' OR ";
            }
        }
        // remove the last OR
        $concat_sql = substr_replace($concat_sql, '', -4);
        $criteria = $concat_sql;
    }
}
// subquery/view string
$subquery_str = '(SELECT DISTINCT bsub.biblio_id, bsub.title, bsub.frequency_id, bsub.last_update FROM biblio AS bsub
    LEFT JOIN biblio_topic AS bt ON bsub.biblio_id = bt.biblio_id
    LEFT JOIN mst_topic AS mt ON bt.topic_id = mt.topic_id WHERE '.$criteria.')';
// table spec
$table_spec = $subquery_str.' AS b
    LEFT JOIN mst_frequency AS fr ON b.frequency_id=fr.frequency_id';
// set table and table header attributes
$datagrid->table_attr = 'class="s-table table"';
$datagrid->table_header_attr = '';
// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, false);
if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
    echo '<div class="infoBox">'.$msg.' : "'.htmlspecialchars($_GET['keywords']).'"</div>';
}
echo $datagrid_result;
/* main content end */
