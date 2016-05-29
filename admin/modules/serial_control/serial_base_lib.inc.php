<?php
/**
 * Copyright (C) 2009  Wardiyono, Arie Nugraha (dicarve@yahoo.com)
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

/* SERIAL CONTROL BASE LIBRARY */

class serial
{
    private $obj_db = null;
    private $serial_id = 0;
    private $serial_date_start;
    private $serial_period;
    private $frequency_time_unit = 'month';
    private $frequency_increment = 30;
    private $biblio_id = 0;

    /**
     * Class constructor
     * @param   object  $obj_db
     * @return  void
     **/
    public function __construct($obj_db, $int_serial_id = 0)
    {
        $this->obj_db = $obj_db;
        $this->serial_id = $int_serial_id;
        // get serial data
        $_serial_q = $this->obj_db->query('SELECT * FROM serial AS sr LEFT JOIN biblio AS b ON sr.biblio_id=b.biblio_id
            LEFT JOIN mst_frequency AS fr ON b.frequency_id=fr.frequency_id WHERE sr.serial_id='.$this->serial_id);
        $_serial_d = $_serial_q->fetch_assoc();
        // set properties
        $this->serial_date_start = $_serial_d['date_start'];
        $this->serial_period = $_serial_d['period'];
        $this->frequency_increment = $_serial_d['time_increment'];
        $this->frequency_time_unit = $_serial_d['time_unit'];
        $this->biblio_id = $_serial_d['biblio_id'];
    }


    /**
     * Generate kardex entry
     * @param   integer $int_total_expl
     * @param   boolean $bool_insert_to_DB
     * @return  array
     **/
    public function generateKardexes($int_total_expl, $bool_insert_to_DB = true)
    {
        if ($int_total_expl < 1) { $int_total_expl = 1; }
        // initialize kardex array
        $_kardex = array();

        $_parsed_date = date_parse($this->serial_date_start);
        $_year = $_parsed_date['year'];
        $_month = $_parsed_date['month'];
        $_day = $_parsed_date['day'];
        $_curr_date = date('Y-m-d');

        for ($_k=0; $_k<$int_total_expl; $_k++) {
            switch ($this->frequency_time_unit) {
                case 'day':
                    $_entry_date = date('Y-m-d', mktime(0, 0, 0, $_month, $_day+($this->frequency_increment*$_k), $_year));
                break;
                case 'week':
                    $_entry_date = date('Y-m-d', mktime(0, 0, 0, $_month, $_day+($this->frequency_increment*7*$_k), $_year));
                break;
                case 'month':
                    $_entry_date = date('Y-m-d', mktime(0, 0, 0, $_month+($this->frequency_increment*$_k), $_day, $_year));
                break;
                case 'year':
                    $_entry_date = date('Y-m-d', mktime(0, 0, 0, $_month, $_day, $_year+($this->frequency_increment*$_k)));
                break;
            }
            $_seq = $_k+1;
            // append to kardex entry array
            $_kardex[$_seq] = $_entry_date;
            // insert to database
            if ($bool_insert_to_DB) {
                @$this->obj_db->query("INSERT INTO kardex (date_expected, seq_number, serial_id, input_date, last_update)
                    VALUES ('$_entry_date', '$_seq', '".$this->serial_id."', '$_curr_date', '$_curr_date')");
            }
        }

        return $_kardex;
    }


    /**
     * Get kardex entries
     * @return  array
     **/
    public function getKardexes()
    {
        $_kardex = array();
        $_kardex_q = $this->obj_db->query('SELECT * FROM kardex WHERE serial_id='.$this->serial_id.' ORDER BY date_expected ASC LIMIT 365');
        while ($_kardex_d = $_kardex_q->fetch_assoc()) {
            $_kardex[] = $_kardex_d;
        }
        return $_kardex;
    }


    /**
     * View kardex list
     * @return  string
     **/
    public function viewKardexes()
    {
        $_can_read = utility::havePrivilege('serial_control', 'r');
        $_can_write = utility::havePrivilege('serial_control', 'w');
        // start the output buffer
        ob_start();
        ?>
        <script type="text/javascript">
        function confirmProcess(int_serial_id, int_kardex_id)
        {
            var confirmBox = confirm('<?php echo addslashes(__('Are you sure to remove selected Kardex data?'));?>' + "\n" + '<?php echo addslashes(__('Once deleted, it can\'t be restored!'));?>');
            if (confirmBox) {
                // set hidden element value
                document.hiddenActionForm.serialID.value = int_serial_id;
                document.hiddenActionForm.remove.value = int_kardex_id;
                // submit form
                document.hiddenActionForm.submit();
            }
        }
        </script>
        <?php
        $_table = new simbio_table();
        $_table->table_attr = 'align="center" class="detailTable" style="width: 100%;" cellpadding="2" cellspacing="0"';
        $_table->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $_table->highlight_row = true;
        $_table->setHeader(array('&nbsp;', __('Date Expected'),
            __('Date Received'), __('Seq. Number'),
            __('Note')));

        if ($_can_read AND $_can_write) {
            $_add_link = '<span title="' . __('Add New Kardex') . '" class="extendLink">&nbsp;</span>';
            $_date_exp = simbio_form_element::dateField('dateExpected[0]');
            $_date_rec = simbio_form_element::dateField('dateReceived[0]');
            $_seq_num = simbio_form_element::textField('text', 'seqNumber[0]', '', 'width: 100%;');
            $_notes = simbio_form_element::textField('text', 'notes[0]', '', 'width: 100%;');

            $_table->appendTableRow(array($_add_link, $_date_exp, $_date_rec, $_seq_num, $_notes));
            $_table->setCellAttr(1, null, 'valign="top" class="alterCell2" style="font-weight: bold; width: auto;"');
            $_table->setCellAttr(1, 0, 'valign="top" class="alterCell2" style="font-weight: bold; width: 5%;"');
            $_table->setCellAttr(1, 1, 'valign="top" class="alterCell2" style="font-weight: bold; width: 25%;"');
            $_table->setCellAttr(1, 2, 'valign="top" class="alterCell2" style="font-weight: bold; width: 25%;"');
        }

        $_row = 2;
        foreach ($this->getKardexes() as $_kardex) {
            // alternate the row color
            $_row_class = ($_row%2 == 0)?'alterCell':'alterCell2';


            if ($_can_read AND $_can_write) {
                // kardex removal links
                $_remove_link = '<a href="#" onclick="confirmProcess('.$this->serial_id.', '.$_kardex['kardex_id'].')" class="trashLink notAJAX">&nbsp;</a>';
                $_date_exp = simbio_form_element::dateField('dateExpected['.$_kardex['kardex_id'].']', $_kardex['date_expected']);
                $_date_rec = simbio_form_element::dateField('dateReceived['.$_kardex['kardex_id'].']', $_kardex['date_received']);
                $_seq_num = simbio_form_element::textField('text', 'seqNumber['.$_kardex['kardex_id'].']', $_kardex['seq_number'], 'width: 100%;');
                $_notes = simbio_form_element::textField('text', 'notes['.$_kardex['kardex_id'].']', $_kardex['notes'], 'width: 100%;');
            } else {
                $_remove_link = '&nbsp;';
                $_date_exp = $_kardex['date_expected']; $_date_rec = $_kardex['date_received'];
                $_seq_num = $_kardex['seq_number']; $_notes = $_kardex['notes'];
            }

            $_table->appendTableRow(array($_remove_link, $_date_exp, $_date_rec, $_seq_num, $_notes));
            $_table->setCellAttr($_row, null, 'valign="top" class="'.$_row_class.'" style="font-weight: bold; width: auto;"');
            $_table->setCellAttr($_row, 0, 'valign="top" class="'.$_row_class.'" style="font-weight: bold; width: 5%;"');
            $_table->setCellAttr($_row, 1, 'valign="top" class="'.$_row_class.'" style="font-weight: bold; width: 25%;"');
            $_table->setCellAttr($_row, 2, 'valign="top" class="'.$_row_class.'" style="font-weight: bold; width: 25%;"');

            $_row++;
        }

        // button
        $_button_grp = '<div style="padding: 3px; background: #999999;"><input type="submit" class="button" name="saveKardexes" value="'.__('Save').'" /></div>';

        // header
        echo '<div style="padding: 5px; background: #CCCCCC;">'.__('Kardex Detail for subscription').' <strong>'.$this->serial_period.'</strong></div>';
        if ($_can_read AND $_can_write) {
            echo '<form method="post" name="kardexListForm" id="kardexListForm" action="'.$_SERVER['PHP_SELF'].'">';
            echo $_button_grp;
        }
        echo $_table->printTable();
        if ($_can_read AND $_can_write) {
            echo $_button_grp;
            echo simbio_form_element::hiddenField('serialID', $this->serial_id);
            echo '</form>';
            // hidden form
            echo '<form name="hiddenActionForm" method="post" action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="serialID" value="0" /><input type="hidden" name="remove" value="0" /></form>';
        }
        /* main content end */
        $_content = ob_get_clean();

        return $_content;
    }


    /**
     * Save kardex data array
     * return   void
     **/
    public function saveKardexes()
    {
        $_serialID = (integer)$_POST['serialID'];
        // iterate trough dateExpected
        foreach ($_POST['dateExpected'] as $_kardexID => $_kardex_d) {
            $_date_expected = trim($this->obj_db->escape_string($_kardex_d));
            if (!$_date_expected) {
                continue;
            }
            $_date_received = trim($this->obj_db->escape_string($_POST['dateReceived'][$_kardexID]));
            $_seq_number = trim($this->obj_db->escape_string($_POST['seqNumber'][$_kardexID]));
            $_notes = trim($this->obj_db->escape_string($_POST['notes'][$_kardexID]));
            // data checking
            $_date_received = !empty($_date_received)?'\''.$_date_received.'\'':'NULL';
            $_seq_number = !empty($_seq_number)?'\''.$_seq_number.'\'':'NULL';
            $_notes = !empty($_notes)?'\''.$_notes.'\'':'NULL';
            // do update
            if ($_kardexID == 0) {
                @$this->obj_db->query("INSERT INTO kardex (serial_id, date_expected, date_received, seq_number, notes) VALUES ($_serialID, '$_date_expected', $_date_received, $_seq_number, $_notes)");
            } else {
                @$this->obj_db->query("UPDATE kardex SET date_expected='$_date_expected',
                    date_received=$_date_received, seq_number=$_seq_number, notes=$_notes WHERE kardex_id=$_kardexID AND serial_id=$_serialID");
            }
        }
    }


    /**
     * Delete kardex data entry
     * @param   integer $int_kardex_id
     * return   void
     **/
    public function deleteKardex($int_kardex_id)
    {
        $_kardexID = (integer)$int_kardex_id;
        $_delete = $this->obj_db->query('DELETE FROM kardex WHERE kardex_id='.$int_kardex_id);
        if ($this->obj_db->affected_rows > 0) {
            return true;
        }
        return false;
    }
}
