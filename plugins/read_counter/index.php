<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 09/03/2021 16:40
 * @File name           : index.php
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

defined('INDEX_AUTH') OR die('Direct access not allowed!');

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB . 'admin/default/session.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');

if (!$can_read) {
    die('<div class="errorBox">' . __('You are not authorized to view this section') . '</div>');
}

function httpQuery($query = [])
{
    return http_build_query(array_unique(array_merge($_GET, $query)));
}

if (isset($_GET['report']))
{
    include __DIR__ . '/report.php';
    exit;
}

$flashError = '';
if (isset($_POST['item_code']) && !empty($_POST['item_code'])) {
    $item_code = utility::filterData('item_code', 'post', true, true, true);
    $stmt = \SLiMS\DB::getInstance()->prepare("SELECT i.item_code, b.title FROM item AS i LEFT JOIN biblio b on i.biblio_id = b.biblio_id WHERE i.item_code = :item_code");
    $stmt->execute(['item_code' => $item_code]);

    if ($stmt->rowCount() > 0) {
        $data = $stmt->fetchObject();
        $stmt = \SLiMS\DB::getInstance()->prepare("INSERT INTO read_counter(item_code, title, created_at, uid) VALUES (:item_code, :title, :created_at, :uid)");
        $stmt->execute(['item_code' => $data->item_code, 'title' => $data->title, 'created_at' => date('Y-m-d H:i:s'), 'uid' => $_SESSION['uid']]);
    } else { $flashError = str_replace('{itemcode}', $item_code, __('No data found with item code {itemcode}.')); }
}

?>

<div class="menuBox">
    <div class="menuBoxInner printIcon">
        <div class="per_title">
            <h2><?php echo __('Read Counter'); ?></h2>
        </div>
        <div class="infoBox">
            <?= __('Enter item code / barcode value into input form below!') ?>
        </div>
        <div class="<?= empty($flashError) ? 'd-none' : 'alert alert-warning font-weight-bold' ?>">
            <?= $flashError ?>
        </div>
        <div class="sub_section">
            <div class="btn-group">
                <a href="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery(['report' => 'yes']) ?>" class="btn btn-default"><?php echo __('Report'); ?></a>
            </div>
            <form name="read_counter" action="<?= $_SERVER['PHP_SELF'] . '?' . httpQuery() ?>" id="search" method="post"
                  class="form-inline"><?php echo __('Barcode'); ?>&nbsp;:&nbsp;
                <input type="text" name="item_code" class="form-control col-md-3" autocomplete="off"/>
                <input type="submit" id="doAdd" value="<?php echo __('Add'); ?>"
                       class="s-btn btn btn-success"/>
            </form>
        </div>
    </div>
</div>

<?php
$grid = new simbio_datagrid('class="table table-striped"');
$grid->setSQLColumn("item_code AS '" . __('Item Code') . "'", "title AS '" . __('Title') . "'", "created_at AS '" . __('Input Date') . "'");
$grid->setSQLorder('created_at DESC');
echo $grid->createDataGrid(\SLiMS\DB::getInstance('mysqli'), 'read_counter');