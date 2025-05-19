<?php

/**
 * File: HasCustomField.php                                                       *
 * Project: Form                                                               *
 * Created Date: Monday, May 19th 2025, 9:46:51 am                             *
 * Author: Waris Agung Widodo <ido.alit@gmail.com>                             *
 * -----                                                                       *
 * Last Modified: Mon May 19 2025                                              *
 * Modified By: Waris Agung Widodo                                             *
 * -----                                                                       *
 * Copyright (c) 2025 Waris Agung Widodo                                       *
 * -----                                                                       *
 * HISTORY:                                                                    *
 * Date      	By	Comments                                                   *
 * ----------	---	---------------------------------------------------------  *
 */

namespace SLiMS\Form;

use SLiMS\DB;

trait HasCustomField
{
    static function getCustomField($key)
    {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM `mst_custom_field` WHERE `primary_table` = :key");
        $stmt->bindParam(':key', $key);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $result;
    }

    static function getCustomDataFromPost($custom_field_key)
    {
        global $sysconf;
        $custom_data = [];
        foreach (self::getCustomField($custom_field_key) as $field) {
            // custom field data
            $cf_dbfield = $field['dbfield'];
            if (isset($_POST[$cf_dbfield])) {
                if (is_array($_POST[$cf_dbfield])) {
                    foreach ($_POST[$cf_dbfield] as $value) {
                        $arr[$value] = $value;
                    }
                    $custom_data[$cf_dbfield] = serialize($arr);
                } else {
                    $cf_val = strip_tags(trim($_POST[$cf_dbfield]), $sysconf['content']['allowable_tags']);
                    if ($field['type'] == 'numeric' && (!is_numeric($cf_val) && $cf_val != '')) {
                        \utility::jsToastr(__('Custom Field'), sprintf(__('Field %s only number for allowed'), $field['label']), 'error');
                        exit();
                    } elseif ($field['type'] == 'date' && $cf_val == '') {
                        \utility::jsToastr(__('Custom Field'), sprintf(__('Field %s is date format, empty not allowed'), $field['label']), 'error');
                        exit();
                    }
                    $custom_data[$cf_dbfield] = $cf_val;
                }
            } else {
                $custom_data[$cf_dbfield] = serialize(array());
            }
        }

        return $custom_data;
    }

    static function getCustomDataFromDatabase($custom_field_key, $key, $id) {
        $table = $custom_field_key . '_custom';
        $db = DB::getInstance();
        $stmt = $db->prepare("select * from `$table` where `$key` = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    function loadCustomField($custom_field_key, $key, $id = null)
    {
        $rec_cust_d = self::getCustomDataFromDatabase($custom_field_key, $key, $id);

        foreach (self::getCustomField($custom_field_key) as $field) {
            $cf_dbfield = $field['dbfield'];
            $cf_label = $field['label'];
            $cf_default = $field['default'];
            $cf_class = $field['class'] ?? '';
            $cf_note = $field['note'] ?? '';
            $cf_data = (isset($field['data']) && $field['data']) ? unserialize($field['data']) : array();

            // get data field record
            if (isset($rec_cust_d[$cf_dbfield]) && isSerialized($rec_cust_d[$cf_dbfield])) {
                try {
                    $unserialized = unserialize($rec_cust_d[$cf_dbfield]);
                    if ($unserialized !== false || $rec_cust_d[$cf_dbfield] === serialize(false)) {
                        $rec_cust_d[$cf_dbfield] = $unserialized;
                    }
                } catch (\Throwable $e) {
                    $rec_cust_d[$cf_dbfield] = null;
                }
            }

            // custom field processing
            if (in_array($field['type'], array('text', 'longtext', 'numeric'))) {
                $cf_max = isset($field['max']) ? $field['max'] : '200';
                $cf_width = isset($field['width']) ? $field['width'] : '50';
                $this->addTextField(($field['type'] == 'longtext') ? 'textarea' : 'text', $cf_dbfield, $cf_label, $rec_cust_d[$cf_dbfield] ?? $cf_default, ' class="form-control ' . $cf_class . '" style="width: ' . $cf_width . '%;" maxlength="' . $cf_max . '"', $cf_note);
            } else if ($field['type'] == 'dropdown') {
                $this->addSelectList($cf_dbfield, $cf_label, $cf_data, $rec_cust_d[$cf_dbfield] ?? $cf_default, ' class="form-control ' . $cf_class . '"');
            } else if ($field['type'] == 'checklist') {
                $this->addCheckBox($cf_dbfield, $cf_label, $cf_data, $rec_cust_d[$cf_dbfield] ?? $cf_default, ' class="form-control ' . $cf_class . '"');
            } else if ($field['type'] == 'choice') {
                $this->addRadio($cf_dbfield, $cf_label, $cf_data, $rec_cust_d[$cf_dbfield] ?? $cf_default, ' class="form-control ' . $cf_class . '"');
            } else if ($field['type'] == 'date') {
                $this->addDateField($cf_dbfield, $cf_label, $rec_cust_d[$cf_dbfield] ?? NULL, ' class="form-control ' . $cf_class . '"');
            }
            unset($cf_data);
        }
        return $this;
    }

    static function insertCustomData($table, $custom_field_key, $key, $id)
    {
        $data = self::getCustomDataFromPost($custom_field_key);
        $data[$key] = $id;
        
        $db = DB::getInstance();

        $fields = array_keys($data);
        $placeholders = array_map(function ($f) {
            return ':' . $f;
        }, $fields);
        $sql = "INSERT INTO `$table` (" . implode(',', $fields) . ") VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $db->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->execute();
        return $db->lastInsertId();
    }

    static function updateCustomData($table, $custom_field_key, $key, $id)
    {
        $data = self::getCustomDataFromPost($custom_field_key);
        $db = DB::getInstance();

        $fields = array_keys($data);
        $placeholders = array_map(function ($f) {
            return $f . '=:' . $f;
        }, $fields);
        $sql = "UPDATE `$table` SET " . implode(',', $placeholders) . " WHERE `$key` = :id";
        $stmt = $db->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    static function saveCustomData($table, $custom_field_key, $key, $id)
    {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM `$table` WHERE `$key` = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            self::updateCustomData($table, $custom_field_key, $key, $id);
        } else {
            self::insertCustomData($table, $custom_field_key, $key, $id);
        }
    }

    static function deleteCustomData($table, $key, $id)
    {
        $db = DB::getInstance();
        $stmt = $db->prepare("DELETE FROM `$table` WHERE `$key` = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }
}
