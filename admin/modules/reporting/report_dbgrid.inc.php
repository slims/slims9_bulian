<?php
/**
 * report_datagrid class
 * SQL Report datagrid creator extended from simbio_datagrid class
 *
 * Copyright (C) 2008 Arie Nugraha (dicarve@yahoo.com)
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

class report_datagrid extends simbio_datagrid
{
    public $paging_set = null;
    public $using_AJAX = false;

    public function __construct()
    {
        // set default table and table header attributes
        $this->table_attr = 'align="center" class="dataListPrinted" cellpadding="3" cellspacing="1"';
        $this->table_header_attr = 'class="dataListHeaderPrinted"';
    }

    /**
     * Modified method to make HTML output more friendly to printer
     *
     * @param   object  $obj_db
     * @param   integer $int_num2show
     * @return  string
     */
    protected function makeOutput($int_num2show = 30)
    {
        // remove invisible field
        parent::removeInvisibleField();
        // disable row highlight
        $this->highlight_row = false;
        // get fields array and set the table header
        $this->setHeader($this->grid_result_fields);

        $_record_row = 1;
        // data loop
        foreach ($this->grid_result_rows as $_data) {
            // alternate the row color
            $_row_class = ($_record_row%2 == 0)?'alterCellPrinted':'alterCellPrinted2';

            // append array to table
            $this->appendTableRow($_data);

            // field style modification
            foreach ($this->grid_result_fields as $_idx => $_fld) {
                // checking for special field width value set by column_width property array
                $_row_attr = 'valign="top"';
                $_classes = $_row_class;
                if (isset($this->column_width[$_idx])) {
                    $_row_attr .= ' style="width: '.$this->column_width[$_idx].';"';
                }
                $this->setCellAttr($_record_row, $_idx, $_row_attr.' class="'.$_classes.'"');
            }
            $_record_row++;
        }

        // init buffer return var
        $_buffer = '';

        // create paging
        if ($this->num_rows > $int_num2show) {
            $this->paging_set = simbio_paging::paging($this->num_rows, $int_num2show, 10, '', 'reportView');
        } else {
            $this->paging_set =  '&nbsp;';
        }
        $_buffer .= '<div class="printPageInfo"><strong>'.$this->num_rows.'</strong> '.__('record(s) found. Currently displaying page').' '.$this->current_page.' ('.$int_num2show.' '.__('record each page').') <a class="printReport" onclick="window.print()" href="#">'.__('Print Current Page').'</a></div>'."\n"; //mfc
        $_buffer .= $this->printTable();

        return $_buffer;
    }
}
