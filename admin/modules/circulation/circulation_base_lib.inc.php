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

/* CIRCULATION BASE LIBRARY */

// be sure that this file not accessed directly
if (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

// define some circulation/loan status
define('LOAN_LIMIT_REACHED', 1);
define('ITEM_NOT_FOUND', 2);
define('ITEM_SESSION_ADDED', 3);
define('ITEM_UNAVAILABLE', 4);
define('TRANS_FLUSH_ERROR', 5);
define('TRANS_FLUSH_SUCCESS', 6);
define('LOAN_NOT_PERMITTED', 7);
define('LOAN_NOT_PERMITTED_PENDING', 8);
define('ITEM_LOAN_FORBID', 9);
define('ITEM_RESERVED', 10);

class circulation extends member
{
    protected $loan_limit = 0;
    protected $loan_periode = 0;
    protected $reborrow_limit = 0;
    protected $fine_each_day = 0;
    protected $item_loan_rules = 0;
    protected $overdue_days = 0;
    protected $grace_periode = 0;
    public $holiday_dayname = array('Sun');
    public $holiday_date = array();
    public $loan_have_overdue = false;
    public $ignore_holidays_fine_calc = '';
    public $error = '';

    /**
     * class constructor
     * @param   object  $obj_db
     * @param   string  $str_member_id
     * @return  void
     **/
    public function __construct($obj_db, $str_member_id)
    {
        parent::__construct($obj_db, $str_member_id);
        $this->loan_limit = intval($this->member_type_prop['loan_limit']);
        $this->loan_periode = intval($this->member_type_prop['loan_periode']);
        $this->reborrow_limit = intval($this->member_type_prop['reborrow_limit']);
        $this->fine_each_day = intval($this->member_type_prop['fine_each_day']);
        $this->grace_periode = intval($this->member_type_prop['grace_periode']);
    }


    /*
     * Set complex loan rules
     * @return  void
     **/
    public function setLoanRules($int_coll_type = 0, $int_gmd_id = 0)
    {
        // if the collection type and gmd is not specified
        // get from the membership type directly
        if (!$int_coll_type AND !$int_gmd_id) {
            return;
        }

        $ctype_string = '';
        if ($int_coll_type) {
            $ctype_string .= ' AND coll_type_id='.intval($int_coll_type).' ';
        }
        $gmd_string = '';
        if ($int_gmd_id) {
            $gmd_string .= ' AND gmd_id='.intval($int_gmd_id).' ';
        }

        // get the data from the loan rules table
        $_loan_rules_q = $this->obj_db->query("SELECT * FROM mst_loan_rules
            WHERE member_type_id=".intval($this->member_type_id)." $ctype_string $gmd_string");
        // check if the loan rules exists
        if ($_loan_rules_q->num_rows > 0) {
            $_loan_rules_d = $_loan_rules_q->fetch_assoc();
            $this->loan_limit = $_loan_rules_d['loan_limit'];
            $this->loan_periode = $_loan_rules_d['loan_periode'];
            $this->reborrow_limit = $_loan_rules_d['reborrow_limit'];
            $this->fine_each_day = $_loan_rules_d['fine_each_day'];
            $this->grace_periode = $_loan_rules_d['grace_periode'];
            $this->item_loan_rules = $_loan_rules_d['loan_rules_id'];
        } else {
            // get data from the loan rules table with collection type specified but GMD not specified
            $_loan_rules_q = $this->obj_db->query("SELECT * FROM mst_loan_rules
                WHERE member_type_id=".intval($this->member_type_id)." $ctype_string");
            // check if the loan rules exists
            if ($_loan_rules_q->num_rows > 0) {
                $_loan_rules_d = $_loan_rules_q->fetch_assoc();
                $this->loan_limit = $_loan_rules_d['loan_limit'];
                $this->loan_periode = $_loan_rules_d['loan_periode'];
                $this->reborrow_limit = $_loan_rules_d['reborrow_limit'];
                $this->fine_each_day = $_loan_rules_d['fine_each_day'];
                $this->grace_periode = $_loan_rules_d['grace_periode'];
                $this->item_loan_rules = $_loan_rules_d['loan_rules_id'];
            } else {
                // get data from the loan rules table with GMD specified but collection type not specified
                $_loan_rules_q = $this->obj_db->query("SELECT * FROM mst_loan_rules
                    WHERE member_type_id=".intval($this->member_type_id)." $gmd_string");
                // check if the loan rules exists
                if ($_loan_rules_q->num_rows > 0) {
                    $_loan_rules_d = $_loan_rules_q->fetch_assoc();
                    $this->loan_limit = $_loan_rules_d['loan_limit'];
                    $this->loan_periode = $_loan_rules_d['loan_periode'];
                    $this->reborrow_limit = $_loan_rules_d['reborrow_limit'];
                    $this->fine_each_day = $_loan_rules_d['fine_each_day'];
                    $this->grace_periode = $_loan_rules_d['grace_periode'];
                    $this->item_loan_rules = $_loan_rules_d['loan_rules_id'];
                }
            }
        }
        // destroy query object
        unset($_loan_rules_q);
    }


    /**
     * Add item to loan session
     * @param   string  $str_item_code
     * @param   boolean $bool_ignore_rules
     * @return  void
     **/
    public function addLoanSession($str_item_code, $bool_ignore_rules = false)
    {
        // you cant borrow any collection if your membership is expired or in pending state
        if ($this->is_expire) {
            return LOAN_NOT_PERMITTED;
        }
        if ($this->is_pending) {
            return LOAN_NOT_PERMITTED_PENDING;
        }
        $_q = $this->obj_db->query("SELECT b.title, i.coll_type_id,
            b.gmd_id, ist.no_loan FROM biblio AS b
            LEFT JOIN item AS i ON b.biblio_id=i.biblio_id
            LEFT JOIN mst_item_status AS ist ON i.item_status_id=ist.item_status_id
            WHERE i.item_code='$str_item_code'");
        $_d = $_q->fetch_row();
        if ($_q->num_rows > 0) {
            // first, check for availability for this item
            $_avail_q = $this->obj_db->query("SELECT item_code FROM loan AS L
                WHERE L.item_code='$str_item_code' AND L.is_lent=1 AND L.is_return=0");
            // if we find any record then it means the item is unavailable
            if ($_avail_q->num_rows > 0) {
                return ITEM_UNAVAILABLE;
            }
            // check loan status for item
            if ((integer)$_d[3] > 0) {
                return ITEM_LOAN_FORBID;
            }
            // check if loan rules are ignored
            if (!defined('IGNORE_LOAN_RULES')) {
                // check if this item is being reserved by other member
                $_resv_q = $this->obj_db->query("SELECT * FROM reserve AS rs
                    WHERE rs.item_code='$str_item_code' AND rs.member_id<>'".$_SESSION['memberID']."'");
                if ($_resv_q->num_rows > 0) {
                    $_resv2_q = $this->obj_db->query("SELECT * FROM reserve AS rs
                        WHERE rs.item_code='$str_item_code' ORDER BY reserve_date ASC LIMIT 1");
                    $_resv2_d = $_resv2_q->fetch_assoc();
                    if ($_resv2_d['member_id'] != $_SESSION['memberID']) {
                        return ITEM_RESERVED;
                    }
                }
            }
            // loan date
            $_loan_date = date('Y-m-d');
            // set loan rules
            self::setLoanRules($_d[1], $_d[2]);
            // calculate due date
            $_due_date = simbio_date::getNextDate($this->loan_periode, $_loan_date);
            $_due_date = simbio_date::getNextDateNotHoliday($_due_date, $this->holiday_dayname, $this->holiday_date);
            // check if due date is not more than member expiry date
            $_expiry_date_compare = simbio_date::compareDates($_due_date, $this->expire_date);
            if ($_expiry_date_compare != $this->expire_date) {
                $_due_date = $this->expire_date;
            }
            $_curr_loan_num = count(parent::getItemLoan($this->item_loan_rules));
            $_curr_session_loan_num = count($_SESSION['temp_loan']);
            // get number of temporay loan session for specific loan rules
            if ($this->item_loan_rules) {
                $_curr_session_loan_num = 0;
                foreach ($_SESSION['temp_loan'] as $loan_session_item) {
                    if ($loan_session_item['loan_rules_id'] == $this->item_loan_rules) {
                        $_curr_session_loan_num++;
                    }
                }
            }

            // check if we ignoring loan rules
            if (defined('IGNORE_LOAN_RULES')) {
                $_SESSION['temp_loan'][$str_item_code] = array(
                    'item_code' => $str_item_code,
                    'loan_rules_id' => $this->item_loan_rules,
                    'title' => $_d[0],
                    'loan_date' => $_loan_date,
                    'due_date' => $_due_date
                );
                return ITEM_SESSION_ADDED;
            } else if ($this->loan_limit > ($_curr_loan_num + $_curr_session_loan_num)) {
                // are the loan limit reached?
                $_SESSION['temp_loan'][$str_item_code] = array(
                    'item_code' => $str_item_code,
                    'loan_rules_id' => $this->item_loan_rules,
                    'title' => $_d[0],
                    'loan_date' => $_loan_date,
                    'due_date' => $_due_date
                );
                return ITEM_SESSION_ADDED;
            } else {
                return LOAN_LIMIT_REACHED;
            }
        } else {
            return ITEM_NOT_FOUND;
        }
    }


    /**
     * Remove item from loan session
     * @param   string  $str_item_code
     * @return  void
     **/
    public function removeLoanSession($str_item_code)
    {
        unset($_SESSION['temp_loan'][$str_item_code]);
    }


    /**
     * Return an item from loan session
     * @param   integer $int_loan_id
     * @return  integer/boolean
     **/
    public function returnItem($int_loan_id)
    {
        $_return_date = date('Y-m-d');
        // check for overdue
        $_fines = self::countOverdueValue($int_loan_id, $_return_date);
        // put data to fines table
        if ($_fines) {
            // set overdue flags
            $this->loan_have_overdue = true;
            $this->overdue_days = $_fines['days'];
            $overdue_description = str_replace("{item_code}", $_fines['item'], __("Overdue fines for item {item_code}"));
            if (is_numeric($this->overdue_days) AND $this->overdue_days > 0) {
                $this->obj_db->query("INSERT INTO fines (fines_date, member_id, debet, credit, description) VALUES('$_return_date', '".$this->member_id ."', ".$_fines['value'].", 0, '". $this->obj_db->escape_string($overdue_description) ."')");
            }
            // add to receipt
            if (isset($_SESSION['receipt_record'])) {
                $_SESSION['receipt_record']['fines'][] = array('days' => $_fines['days'], 'value' => $_fines['value']);
            }
        }
        // update the loan data
        $this->obj_db->query("UPDATE loan SET is_return=1, return_date='$_return_date', last_update='".date("Y-m-d H:i:s")."' WHERE loan_id=$int_loan_id AND member_id='".$this->member_id."' AND is_lent=1 AND is_return=0");

        // Update loan history (replaces update_loan_history trigger)
        $this->updateLoanHistory($int_loan_id, array(
            'is_return' => 1,
            'return_date' => $_return_date
        ));

        // add to receipt
        if (isset($_SESSION['receipt_record'])) {
            // get item data
            $_title_q = $this->obj_db->query('SELECT b.title, b.classification, l.* FROM loan AS l
                LEFT JOIN item AS i ON l.item_code=i.item_code
                INNER JOIN biblio AS b ON i.biblio_id=b.biblio_id WHERE l.loan_id='.$int_loan_id);
            $_title_d = $_title_q->fetch_assoc();
            $_returns = array();
            $_returns = $_title_d;
            $_returns['overdues'] = $_fines;
            $_returns['itemCode'] = $_title_d['item_code'];
            $_returns['returnDate'] = $_return_date;
            $_SESSION['receipt_record']['return'][] = $_returns;
        }
        // check if this item is being reserved by other member
        $_resv_q = $this->obj_db->query("SELECT l.item_code FROM reserve AS rs
            INNER JOIN loan AS l ON rs.item_code=l.item_code
            WHERE l.loan_id=$int_loan_id AND rs.member_id!='".$this->member_id."'");
        if ($_resv_q->num_rows > 0) {
            return ITEM_RESERVED;
        }
        return true;
    }



    /**
     * extend item loan
     * @param   integer $int_loan_id
     * @return  integer/boolean
     **/
    public function extendItemLoan($int_loan_id)
    {
        // check if this item is being reserved by other member
        $_resv_q = $this->obj_db->query("SELECT l.item_code FROM reserve AS rs
            INNER JOIN loan AS l ON rs.item_code=l.item_code
            WHERE l.loan_id=$int_loan_id AND rs.member_id!='".$_SESSION['memberID']."'");
        if ($_resv_q->num_rows > 0) {
            return ITEM_RESERVED;
        }
        // return this item first
        self::returnItem($int_loan_id);
        // get loan rules for this loan
        $_loan_rules_q = $this->obj_db->query("SELECT loan_periode FROM mst_loan_rules AS lr LEFT JOIN
            loan AS l ON lr.loan_rules_id=l.loan_rules_id WHERE loan_id=$int_loan_id");
        if ($_loan_rules_q->num_rows > 0) {
            $_loan_rules_d = $_loan_rules_q->fetch_row();
            $this->loan_periode = $_loan_rules_d[0];
        }
        // due date
        $_loan_date = date('Y-m-d');
        // calculate due date
        $_due_date = simbio_date::getNextDate($this->loan_periode, $_loan_date);
        $_due_date = simbio_date::getNextDateNotHoliday($_due_date, $this->holiday_dayname, $this->holiday_date);
        // check if due date is not more than member expiry date
        $_expiry_date_compare = simbio_date::compareDates($_due_date, $this->expire_date);
        if ($_expiry_date_compare != $this->expire_date) {
            $_due_date = $this->expire_date;
        }
        $query = $this->obj_db->query("UPDATE loan SET renewed=renewed+1, due_date='$_due_date', is_return=0
            WHERE loan_id=$int_loan_id AND member_id='".$this->member_id."'");

        $_renewed_q = $this->obj_db->query("SELECT renewed FROM loan WHERE loan_id=$int_loan_id");
        $_renewed_d = $_renewed_q->fetch_row();
        $new_renewed_count = intval($_renewed_d[0]);

        // Update loan history (replaces update_loan_history trigger)
        $this->updateLoanHistory($int_loan_id, array(
            'renewed' => $new_renewed_count,
            'is_return' => 0
        ));

        $_SESSION['reborrowed'][] = $int_loan_id;
        // add to receipt
        if (isset($_SESSION['receipt_record'])) {
            // get item data
            $_title_q = $this->obj_db->query('SELECT b.title, b.classification, l.* FROM loan AS l
                LEFT JOIN item AS i ON l.item_code=i.item_code
                INNER JOIN biblio AS b ON i.biblio_id=b.biblio_id WHERE l.loan_id='.$int_loan_id);
            $_title_d = $_title_q->fetch_assoc();
            $_loans = array();
            $_loans = $_title_d;
            $_loans['itemCode'] = $_title_d['item_code'];
            $_loans['loanDate'] = $_loan_date;
            $_loans['dueDate'] = $_due_date;
            $_SESSION['receipt_record']['extend'][] = $_loans;
        }
        return true;
    }


    /**
     * count overdue value
     * @param   integer $int_loan_id
     * @param   string  $str_return_date
     * @return  boolean|string|integer
     **/
    public function countOverdueValue($int_loan_id, $str_return_date)
    {
        $_on_grace_periode = false;
        // get due date for this loan
        $_loan_q = $this->obj_db->query("SELECT l.due_date, l.loan_rules_id, l.item_code FROM loan AS l WHERE loan_id=$int_loan_id");
        $_loan_d = $_loan_q->fetch_row();
        // compare dates
        $_date = simbio_date::compareDates($str_return_date, $_loan_d[0]);
        if ($_date == $str_return_date) {
            // how many days the overdue
            $_overdue_days = simbio_date::calcDay($str_return_date, $_loan_d[0]);

            /* modified by Indra Sutriadi */
            if ($this->ignore_holidays_fine_calc === true || $this->ignore_holidays_fine_calc > 0) {
                // count holiday and subtract it from overdue days
                $_holiday_count = simbio_date::countHolidayBetween($_loan_d[0], $str_return_date, $this->holiday_dayname, $this->holiday_date);
                $_overdue_days_ignore_holiday = $_overdue_days-$_holiday_count;
                // prevent negative value, if $_holiday_count > $_overdue_days
                if ($_overdue_days_ignore_holiday < 0) return false;
            }
            /* end of modification */

            if ($_overdue_days < 1) {
                return false;
            }
            // check for grace periode
            if (!empty($this->grace_periode)) {
                $_due_plus_grace_date = simbio_date::getNextDate($this->grace_periode, $_loan_d[0]);
                $_latest_date = simbio_date::compareDates($str_return_date, $_due_plus_grace_date);
                if ($_latest_date == $_due_plus_grace_date) {
                    $_on_grace_periode = true;
                }
            }
            // check for loan rules if any
            if (!empty($_loan_d[1])) {
                $_loan_rules_q = $this->obj_db->query('SELECT fine_each_day, grace_periode FROM mst_loan_rules WHERE loan_rules_id='.$_loan_d[1]);
                $_loan_rules_d = $_loan_rules_q->fetch_row();
                $this->fine_each_day = $_loan_rules_d[0];
                // check for grace periode
                if (!empty($_loan_rules_d[1])) {
                    $_due_plus_grace_date = simbio_date::getNextDate($_loan_rules_d[1], $_loan_d[0]);
                    $_latest_date = simbio_date::compareDates($str_return_date, $_due_plus_grace_date);
                    if ($_latest_date == $_due_plus_grace_date) {
                        $_on_grace_periode = true;
                    }
                }
            }
            // calculate fines value
            if ($_on_grace_periode) {
                return array('days' => 'On Grace Periode', 'value' => 0, 'item' => $_loan_d[2]);
            } else {
                $_fines_value = $this->fine_each_day*$_overdue_days;
                if (isset($_overdue_days_ignore_holiday)){
                    $_fines_value = $this->fine_each_day*$_overdue_days_ignore_holiday;
                    $_overdue_days = $_overdue_days_ignore_holiday;
                }
                return array('days' => $_overdue_days, 'value' => $_fines_value, 'item' => $_loan_d[2]);
            }
        }
        return false;
    }


    /**
     * Get overdue days
     * @return  integer
     **/
    public function getOverdueDays()
    {
        return $this->overdue_days;
    }


    /**
     * Convert literal values from simbio_dbop to actual values
     * @param   mixed $value
     * @return  mixed
     **/
    protected function convertLiteralValue($value)
    {
        if (is_string($value) && strpos($value, 'literal{') === 0) {
            // Extract value from literal{value} format
            return intval(str_replace(['literal{', '}'], '', $value));
        }
        return $value;
    }


    /**
     * Insert loan history record (replaces insert_loan_history trigger)
     * @param   integer $loan_id
     * @param   array   $loan_data
     * @return  boolean
     **/
    protected function insertLoanHistory($loan_id, $loan_data)
    {
        // Get additional bibliographic information
        $query = "SELECT
            b.title,
            b.biblio_id,
            IF(i.call_number IS NULL, b.call_number, i.call_number) as call_number,
            b.classification,
            g.gmd_name,
            l.language_name,
            ml.location_name,
            mct.coll_type_name,
            m.member_name,
            mmt.member_type_name
        FROM item i
        LEFT JOIN biblio b ON i.biblio_id=b.biblio_id
        LEFT JOIN mst_gmd g ON g.gmd_id=b.gmd_id
        LEFT JOIN mst_language l ON b.language_id=l.language_id
        LEFT JOIN mst_location ml ON i.location_id=ml.location_id
        LEFT JOIN mst_coll_type mct ON i.coll_type_id=mct.coll_type_id
        LEFT JOIN member m ON m.member_id='{$loan_data['member_id']}'
        LEFT JOIN mst_member_type mmt ON m.member_type_id=mmt.member_type_id
        WHERE i.item_code='{$loan_data['item_code']}'";

        $result = $this->obj_db->query($query);
        if ($result && $result->num_rows > 0) {
            $biblio_data = $result->fetch_assoc();

            // Prepare loan history data with proper escaping and handle literal values
            $history_data = array(
                'loan_id' => $loan_id,
                'item_code' => $this->obj_db->escape_string($loan_data['item_code']),
                'member_id' => $this->obj_db->escape_string($loan_data['member_id']),
                'loan_date' => $loan_data['loan_date'],
                'due_date' => $loan_data['due_date'],
                'renewed' => $this->convertLiteralValue($loan_data['renewed'] ?? 0),
                'is_lent' => isset($loan_data['is_lent']) ? $loan_data['is_lent'] : 1,
                'is_return' => $this->convertLiteralValue($loan_data['is_return'] ?? 0),
                'return_date' => isset($loan_data['return_date']) ? $loan_data['return_date'] : 'NULL',
                'input_date' => $loan_data['input_date'],
                'last_update' => $loan_data['last_update'],
                'title' => $this->obj_db->escape_string($biblio_data['title'] ?? ''),
                'biblio_id' => $biblio_data['biblio_id'] ?? 0,
                'call_number' => $this->obj_db->escape_string($biblio_data['call_number'] ?? ''),
                'classification' => $this->obj_db->escape_string($biblio_data['classification'] ?? ''),
                'gmd_name' => $this->obj_db->escape_string($biblio_data['gmd_name'] ?? ''),
                'language_name' => $this->obj_db->escape_string($biblio_data['language_name'] ?? ''),
                'location_name' => $this->obj_db->escape_string($biblio_data['location_name'] ?? ''),
                'collection_type_name' => $this->obj_db->escape_string($biblio_data['coll_type_name'] ?? ''),
                'member_name' => $this->obj_db->escape_string($biblio_data['member_name'] ?? ''),
                'member_type_name' => $this->obj_db->escape_string($biblio_data['member_type_name'] ?? '')
            );

            // Build INSERT query
            $fields = array();
            $values = array();
            foreach ($history_data as $field => $value) {
                $fields[] = $field;
                if ($value === 'NULL' || $value === null) {
                    $values[] = 'NULL';
                } else {
                    $values[] = "'" . $value . "'";
                }
            }

            $insert_query = "INSERT INTO loan_history (" . implode(', ', $fields) . ")
                           VALUES (" . implode(', ', $values) . ")";

            $insert_result = $this->obj_db->query($insert_query);
            return $insert_result !== false;
        }

        return false;
    }


    /**
     * Update loan history record (replaces update_loan_history trigger)
     * @param   integer $loan_id
     * @param   array   $updated_data
     * @return  boolean
     **/
    protected function updateLoanHistory($loan_id, $updated_data)
    {
        $update_fields = array();

        // Map the fields that should be updated in loan_history
        $updatable_fields = array(
            'is_lent' => 'is_lent',
            'is_return' => 'is_return',
            'renewed' => 'renewed',
            'return_date' => 'return_date'
        );

        foreach ($updatable_fields as $loan_field => $history_field) {
            if (isset($updated_data[$loan_field])) {
                $value = $updated_data[$loan_field];
                if ($value === null || $value === 'NULL') {
                    $update_fields[] = "$history_field=NULL";
                } elseif ($loan_field === 'renewed' && is_numeric($value)) {
                    $update_fields[] = "$history_field=" . intval($value);
                } else {
                    $update_fields[] = "$history_field='" . $this->obj_db->escape_string($value) . "'";
                }
            }
        }

        if (!empty($update_fields)) {
            $update_query = "UPDATE loan_history SET " . implode(', ', $update_fields) . " WHERE loan_id=$loan_id";
            $update_result = $this->obj_db->query($update_query);
            return $update_result !== false;
        }

        return true;
    }


    /**
     * Delete loan history record (replaces delete_loan_history trigger)
     * @param   integer $loan_id
     * @return  boolean
     **/
    protected function deleteLoanHistory($loan_id)
    {
        $delete_query = "DELETE FROM loan_history WHERE loan_id=$loan_id";
        $delete_result = $this->obj_db->query($delete_query);
        return $delete_result !== false;
    }


    /**
     * Finish loan transaction session
     * @return  void
     **/
    public function finishLoanSession()
    {
        // receipt
        if (isset($_SESSION['receipt_record'])) {
            if (isset($_SESSION['receipt_record']['return']) || isset($_SESSION['receipt_record']['extend']) || isset($_SESSION['temp_loan'])) {
                $_SESSION['receipt_record']['memberID'] = $this->member_id;
                $_SESSION['receipt_record']['memberName'] = $this->member_name;
                $_SESSION['receipt_record']['memberType'] = $this->member_type_name;
                $_SESSION['receipt_record']['date'] = date('Y-m-d H:i:s');
            } else {
                unset($_SESSION['receipt_record']);
            }
        }
        // count number of loans
        if (count($_SESSION['temp_loan']) > 0) {
            $error_num = 0;
            foreach ($_SESSION['temp_loan'] as $loan_item) {
                // insert loan data to database
                if ($loan_item['loan_rules_id']) {
                    $data['loan_rules_id'] = $loan_item['loan_rules_id'];
                } else {
                    $data['loan_rules_id'] = 'literal{0}';
                }
                $data['item_code'] = $loan_item['item_code'];
                $data['member_id'] = $this->member_id;
                $data['loan_date'] = $loan_item['loan_date'];
                $data['due_date'] = $loan_item['due_date'];
                $data['renewed'] = 'literal{0}';
                $data['is_lent'] = 1;
                $data['is_return'] = 'literal{0}';
                $data['input_date'] = date("Y-m-d H:i:s");
                $data['last_update'] = date("Y-m-d H:i:s");
                $data['uid'] = $_SESSION['uid'];
                try {
                    $sql_op = new simbio_dbop($this->obj_db);
                    if ($sql_op->insert('loan', $data)) {
                        # get last insert id (loan_id)
                        $loan_id = $this->obj_db->insert_id;

                        // Insert loan history (replaces insert_loan_history trigger)
                        $this->insertLoanHistory($loan_id, $data);

                        if (isset($_SESSION['receipt_record'])) {
                            // get title
                            $_title_q = $this->obj_db->query('SELECT title, classification FROM biblio AS b INNER JOIN item AS i ON b.biblio_id=i.biblio_id WHERE i.item_code=\''.$data['item_code'].'\'');
                            $_title_d = $_title_q->fetch_row();
                            $_title = $_title_d[0];
                            $_classification = $_title_d[1];
                            // add to receipt
                            $data_loan = (array)circapi::loan_load_by_id($this->obj_db, $loan_id);
                            $_loans = array ();
                            $_loans = $data_loan;
                            $_loans['itemCode'] = $data['item_code'];
                            $_loans['title'] = $_title;
                            $_loans['classification'] = $_classification;
                            $_loans['loanDate'] = $data['loan_date'];
                            $_loans['dueDate'] = $data['due_date'];
                            $_SESSION['receipt_record']['loan'][] = $_loans;
                        }
                        // remove any reservation related to this items
                        @$this->obj_db->query('DELETE FROM reserve WHERE member_id=\''.$this->member_id.'\' AND item_code=\''.$data['item_code'].'\'');
                    } else {
                        throw new Exception($sql_op->error);
                    }
                } catch (Exception $e) {
                    $this->error = $e->getMessage();
                    $error_num++;
                }
            }
            // clean all circulation sessions
            $_SESSION['temp_loan'] = array();
            $_SESSION['reborrowed'] = array();
            unset($_SESSION['memberID']);
            // return the status
            if ($error_num) {
                return TRANS_FLUSH_ERROR;
            } else {
                return TRANS_FLUSH_SUCCESS;
            }
        } else {
            // clean all circulation sessions
            $_SESSION['temp_loan'] = array();
            $_SESSION['reborrowed'] = array();
            unset($_SESSION['memberID']);
        }
    }
}