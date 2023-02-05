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


/* Item data export section */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-bibliography');
// start the session
require SB.'admin/default/session.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You are not authorized to view this section').'</div>');
}

if (isset($_POST['doExport'])) {
    // check for form validity
    if (empty($_POST['fieldSep']) OR empty($_POST['fieldEnc'])) {
        utility::jsToastr('Item Export', __('Required fields (*)  must be filled correctly!'), 'error');
        exit();
    } else {
        // set PHP time limit
        set_time_limit(0);

        // create local function to fetch values
        function getValues($obj_db, $str_query)
        {
            // make query from database
            $_value_q = $obj_db->query($str_query);
            if ($_value_q->num_rows > 0) {
                $_value_buffer = '';
                while ($_value_d = $_value_q->fetch_row()) {
                    if ($_value_d[0]) {
                        $_value_buffer .= '<'.$_value_d[0].'>';
                    }
                }
                return $_value_buffer;
            }
            return null;
        }

        // limit
        $sep = trim($_POST['fieldSep']);
        $encloser = trim($_POST['fieldEnc']);
        $limit = intval($_POST['recordNum']);
        $offset = intval($_POST['recordOffset']);
        if ($_POST['recordSep'] === 'NEWLINE') {
            $rec_sep = "\n";
        } else if ($_POST['recordSep'] === 'RETURN') {
            $rec_sep = "\r";
        } else {
            $rec_sep = trim($_POST['recordSep']);
        }
        // fetch all data from item table
        $sql = "SELECT
            i.item_code, i.call_number, ct.coll_type_name,
            i.inventory_code, i.received_date, spl.supplier_name,
            i.order_no, loc.location_name,
            i.order_date, st.item_status_name, i.site,
            i.source, i.invoice, i.price, i.price_currency, i.invoice_date,
            i.input_date, i.last_update, b.title
            FROM item AS i
            LEFT JOIN biblio AS b ON i.biblio_id=b.biblio_id
            LEFT JOIN mst_coll_type AS ct ON i.coll_type_id=ct.coll_type_id
            LEFT JOIN mst_supplier AS spl ON i.supplier_id=spl.supplier_id
            LEFT JOIN mst_item_status AS st ON i.item_status_id=st.item_status_id
            LEFT JOIN mst_location AS loc ON i.location_id=loc.location_id ";
        if ($limit > 0) { $sql .= ' LIMIT '.$limit; }
        if ($offset > 1) {
            if ($limit > 0) {
                $sql .= ' OFFSET '.($offset-1);
            } else {
                $sql .= ' LIMIT '.($offset-1).',99999999999';
            }
        }
        // for debugging purpose only
        // die($sql);
        $all_data_q = $dbs->query($sql);
        if ($dbs->error) {
            utility::jsToastr('Item Export', __('Error on query to database, Export FAILED!'.$dbs->error), 'error');
        } else {
            if ($all_data_q->num_rows > 0) {
                header('Content-type: text/plain');
                header('Content-Disposition: attachment; filename="senayan_item_export.csv"');
                $headers = [];
                $itemData = [];
                while ($item_d = $all_data_q->fetch_assoc()) {
                    $buffer = null;
                    foreach ($item_d as $key => $fld_d) {
                        $headers[$key] = $key;
                        $fld_d = $dbs->escape_string($fld_d??'');
                        // data
                        $buffer .=  stripslashes($encloser.$fld_d.$encloser);
                        // field separator
                        $buffer .= $sep;
                    }
                    $itemData[] = substr_replace($buffer, '', -1);
                }

                $header_buffer = '';
                foreach ($headers as $header) {
                  $header_buffer .= $encloser.$header.$encloser.$sep;
                }
                $header_buffer .= $rec_sep;

                $item_buffer = '';
                foreach ($itemData as $item) {
                  $item_buffer .= $item.$rec_sep;
                }

                if (isset($_POST['header'])) echo $header_buffer;
                echo $item_buffer;
                exit();
            } else {
                utility::jsToastr('Item Export', __('There is no record in item database yet, Export FAILED!'), 'error');
            }
        }
    }
    exit();
}
?>
<div class="menuBox">
<div class="menuBoxInner exportIcon">
	<div class="per_title">
    	<h2><?php echo __('Item Export Tool'); ?></h2>
	</div>
	<div class="infoBox">
    <?php echo __('Export item data to CSV file'); ?>
	</div>
</div>
</div>
<?php

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="doExport" value="'.__('Export Now').'" class="s-btn btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// field separator
$form->addTextField('text', 'fieldSep', __('Field Separator').'*', ''.htmlentities(',').'', 'style="width: 10%;" maxlength="3" class="form-control"');
//  field enclosed
$form->addTextField('text', 'fieldEnc', __('Field Enclosed With').'*', ''.htmlentities('"').'', 'style="width: 10%;" class="form-control"');
// record separator
$rec_sep_options[] = array('NEWLINE', 'NEWLINE');
$rec_sep_options[] = array('RETURN', 'CARRIAGE RETURN');
$form->addSelectList('recordSep', __('Record Separator'), $rec_sep_options,'','class="form-control col-3"');
// number of records to export
$form->addTextField('text', 'recordNum', __('Number of Records To Export (0 for all records)'), '0', 'style="width: 10%;" class="form-control"');
// records offset
$form->addTextField('text', 'recordOffset', __('Start From Record'), '1', 'style="width: 10%;" class="form-control"');
// header (column name)
$form->addCheckBox('header', __('Put columns names in the first row'), array( array('1', __('Yes')) ), '');
// output the form
echo $form->printOut();
