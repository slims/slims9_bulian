<?php
/**
 *
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

// be sure that this file not accessed directly
if (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

// start the output buffer
ob_start();
$table = new simbio_table();
$table->table_attr = 'align="center" class="detailTable noAutoFocus" style="width: 100%;" cellpadding="2" cellspacing="0"';
$table->setHeader(array(__('Module Name'), '<a id="allRead" class="notAJAX" href="#">'.__('Read').'</a>', '<a id="allWrite" class="notAJAX" href="#">'.__('Write').'</a>'));
$table->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';

// initial row count
$row = 1;
$row_class = 'alterCell2';

// database list
$module_query = $dbs->query("SELECT * FROM mst_module AS m");
while ($module_data = $module_query->fetch_assoc()) {
    // alternate the row color
    if ($row_class == 'alterCell2') {
        $row_class = 'alterCell';
    } else {
        $row_class = 'alterCell2';
    }

    $read_checked = '';
    $write_checked = '';

    if (isset($priv_data[$module_data['module_id']]['r']) AND $priv_data[$module_data['module_id']]['r'] == 1) {
        $read_checked = 'checked';
    }

    if (isset($priv_data[$module_data['module_id']]['w']) AND $priv_data[$module_data['module_id']]['w'] == 1) {
        $read_checked = 'checked';
        $write_checked = 'checked';
    }

    $chbox_read = '<input type="checkbox" class="read" name="read[]" value="'.$module_data['module_id'].'" '.$read_checked.' />';
    $chbox_write = '<input type="checkbox" class="write" name="write[]" value="'.$module_data['module_id'].'" '.$write_checked.' />';

    $table->appendTableRow(array(__( ucwords(str_replace('_', ' ', $module_data['module_name'])) ), $chbox_read, $chbox_write));
    $table->setCellAttr($row, 0, 'valign="top" class="'.$row_class.'" style="font-weight: bold;"');
    $table->setCellAttr($row, 1, 'valign="top" class="'.$row_class.'" style="width: 5%;"');
    $table->setCellAttr($row, 2, 'valign="top" class="'.$row_class.'" style="width: 5%;"');

    $row++;
}

echo $table->printTable();
ob_start();
?>
<script type="text/javascript">
  $(document).ready(function() {

   // function to toggle check input:checkbox element
   var toggleChecked = function ($cls) {
     var elm = $('input:checkbox.' + $cls);
     var isChecked = elm.is(':checked');
     if (isChecked) {
      elm.prop('checked', false);
     } else {
      elm.prop('checked', true);
     }
   };

   // toggle checked for all read rule
   $('#allRead').click(function (e) {
     e.preventDefault();
     toggleChecked('read');
   });

   // toggle checked for all write rule
   $('#allWrite').click(function (e) {
     e.preventDefault();
     toggleChecked('write');
   });

  });
</script>
<?php
echo ob_get_clean();
$priv_table = ob_get_clean();
