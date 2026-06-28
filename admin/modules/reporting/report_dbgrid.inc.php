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
    public $show_spreadsheet_export = false;
    public $spreadsheet_export_btn = '';
    public $show_pdf_export = false;
    public $pdf_export_btn = '';

    public function __construct()
    {
        // set default table and table header attributes
        $this->table_attr = 'align="center" class="dataListPrinted" cellpadding="3" cellspacing="1"';
        $this->table_header_attr = 'class="dataListHeaderPrinted"';
        $this->spreadsheet_export_btn = '<a href="' . MWB . 'reporting/spreadsheet.php" class="s-btn btn btn-default">'.__('Export to spreadsheet format').'</a>';
        $this->pdf_export_btn = '<a href="' . MWB . 'reporting/pdf.php" class="s-btn btn btn-default">'.__('Export to PDF format').'</a>';
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
        // debug box
        if (isDev() !== false) {
            debugBox(content: function() use ($_buffer) {
                debug($this->sql_str);
            });
        }
        $_buffer .= '<div class="s-print__page-info printPageInfo"><strong>'.$this->num_rows.'</strong> '.__('record(s) found. Currently displaying page').' '.$this->current_page.' ('.$int_num2show.' '.__('record each page').') <a class="s-btn btn btn-default printReport" onclick="window.print()" href="#">'.__('Print Current Page').'</a>';

        $_button_group = '';

        $svg_spreadsheet = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-spreadsheet" viewBox="0 0 16 16"><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M2 2a1 1 0 0 1 1-1h5.5v2h2v.5a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2zm5 12h1V9H5v1h2v4zM5 8h2V5H5v3zm4 0h2V5H9v3zm2 6h1V9H9v1h2v4z"/></svg>';

        $svg_pdf = '<svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16"><path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M2 2a1 1 0 0 1 1-1h5.5v2h2v.5a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2z"/><path d="M4.603 14.087a.8.8 0 0 1-.438-.078.6.6 0 0 1-.29-.255.4.4 0 0 1-.087-.29l.07-1.314h-.35c-.178 0-.272-.084-.336-.182a.7.7 0 0 1-.08-.344c.007-.442.2-.75.556-1.077 0 0 .2-.17.518-.328a3 3 0 0 0 .54-1.46c.01-.2.02-.45.02-.74v-.12c0-.525-.093-.972-.346-1.353a2.5 2.5 0 0 0-.845-.92c-.37-.29-.75-.483-1.127-.585A.7.7 0 0 1 4 4.093v.259c.09.112.213.245.39.388.196.14.417.332.628.565a.8.8 0 0 1 .158.337c.045.163.078.332.078.533v.123c0 .329-.028.618-.088.804-.047.162-.128.286-.247.375a.5.5 0 0 1-.365.093c-.097 0-.196-.04-.26-.145a.79.79 0 0 1-.086-.346v-.966c.002-.27.086-.5.214-.666a.54.54 0 0 1 .314-.19.74.74 0 0 1 .375.05.8.8 0 0 1 .286.138 1.48 1.48 0 0 1 .4.4.95.95 0 0 1 .19.606c0 .485-.145.748-.432.997a.78.78 0 0 0-.256.284 1.15 1.15 0 0 0-.177.568v.077c0 .243.013.398.053.526.04.13.125.2.222.25.096.05.195.078.307.078.232 0 .4-.143.543-.448l.156-.397c.504.415 1.075.815 1.708 1.154.27.145.42.247.515.314.43.332.55.71.49 1.178-.052.4-.33.69-.765.875a1.5 1.5 0 0 1-1.32.067c-.2-.07-.4-.183-.603-.341zM8 12.5a1 1 0 0 0-1-1H5.75a.75.75 0 0 1 0-1h.5a.75.75 0 0 1 0-1h-.5a.75.75 0 0 1 0-1h1.5a.5.5 0 0 0 0-1h-2a.5.5 0 0 0 0 1H4.5a.5.5 0 0 0 0 1H5a.5.5 0 0 0 0 1H4.5a.5.5 0 0 0 0 1h.5a.75.75 0 0 1 0 1h-.5a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1z"/></svg>';

        if($this->show_spreadsheet_export) {
            $_button_group .= '<a href="' . MWB . 'reporting/spreadsheet.php" class="s-btn btn btn-default">' . $svg_spreadsheet . ' ' . __('Export Spreadsheet') . '</a>';
        }
        if($this->show_pdf_export) {
            $_button_group .= '<a href="' . MWB . 'reporting/pdf.php" class="s-btn btn btn-default">' . $svg_pdf . ' ' . __('Export PDF') . '</a>';
        }

        if (!empty($_button_group)) {
            $_buffer .= '<div class="btn-group s-datagrid-btn-group" role="group" aria-label="Export options">';
            $_buffer .= $_button_group;
            $_buffer .= '</div>';
        }
        $_buffer .= '</div>'."\n"; //mfc
        $_buffer .= $this->printTable();

        return $_buffer;
    }
}