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

/* Member data export section */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-membership');
// start the session
require SB.'admin/default/session.inc.php';
require SB.'admin/default/session_check.inc.php';
require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';

// privileges checking
$can_read = utility::havePrivilege('membership', 'r');
$can_write = utility::havePrivilege('membership', 'w');

if (!$can_read) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to access this area!').'</div>');
}

if (isset($_POST['doExport'])) {
    // check for form validity
    if (empty($_POST['fieldSep']) OR empty($_POST['fieldEnc'])) {
        utility::jsAlert(__('Required fields (*)  must be filled correctly!'));
        exit();
    } else {
        // set PHP time limit
        set_time_limit(3600);
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
        // fetch all data from biblio table
        $sql = "SELECT
            m.member_id, m.member_name, m.gender,
            mt.member_type_name, m.member_email, m.member_address,
            m.postal_code, m.inst_name, m.is_new,
            m.member_image, m.pin, m.member_phone,
            m.member_fax, m.member_since_date, m.register_date,
            m.expire_date, m.birth_date, m.member_notes
            FROM member AS m
            LEFT JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id ";
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
            utility::jsAlert(__('Error on query to database, Export FAILED!'));
        } else {
            if ($all_data_q->num_rows > 0) {
                header('Content-type: text/plain');
                header('Content-Disposition: attachment; filename="senayan_member_export.csv"');
                $headers = [];
                $itemData = [];
                while ($member_data = $all_data_q->fetch_assoc()) {
                    $buffer = null;
                    foreach ($member_data as $key => $fld_data) {
                        $headers[$key] = $key;
                        $fld_data = $dbs->escape_string($fld_data??'');
                        // data
                        $buffer .=  $encloser.$fld_data.$encloser;
                        // field separator
                        $buffer .= $sep;
                    }
                    // remove the last field separator
                    $buffer = substr_replace($buffer, '', -1);
                    $itemData[] = $buffer;
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
                utility::jsAlert(__('There is no record in membership database yet, Export FAILED!'));
            }
        }
    }
    exit();
}

?>
<div class="menuBox">
<div class="menuBoxInner exportIcon">
	<div class="per_title">
    	<h2><?php echo __('Export Data'); ?></h2>
    </div>
    <div class="infoBox">
    	<?php echo __('Export member(s) data to CSV file'); ?>
    </div>
</div>
</div>
<?php

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'].'', 'post');
$form->submit_button_attr = 'name="doExport" value="'.__('Export Now').'" class="s-btn btn btn-primary"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

/* Form Element(s) */
// field separator
$form->addTextField('text', 'fieldSep', __('Field Separator').'*', ''.htmlentities(',').'', 'style="width: 10%;" class="form-control"');
//  field enclosed
$form->addTextField('text', 'fieldEnc', __('Field Enclosed With'), ''.htmlentities('"').'', 'style="width: 10%;" class="form-control"');
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
