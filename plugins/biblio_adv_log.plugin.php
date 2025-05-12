<?php
/**
 * Plugin Name: Bibliography Log - Advanced
 * Plugin URI: https://github.com/slims/slims9_bulian
 * Description: Add more detail for biblio log. This is an example of a plugin that uses the hooking method.
 * Version: 1.0.0
 * Author: Waris Agung Widodo
 * Author URI: https://github.com/idoalit
 */

use SLiMS\DB;

/**
 *  Prepare variable to storing old data
 */
$old_data = [];

/**
 * Registering hook plugin on bibliography before updated
 * In this hook, we will get the old data.
 */
$this->register('bibliography_before_update', function ($data) use (&$old_data) {
    // api still uses mysqli driver, so we use the mysqli instance instead of pdo
    $old_data = api::biblio_load(DB::getInstance('mysqli'), $data['biblio_id']);
});

/**
 * Registering hook on bibliography after updated
 * In this hook, we will compare old data with new data per field
 */
$this->register('bibliography_after_update', function ($data) use (&$old_data) {

    // because old data get data from api, we will get new data from api too
    $new_data = api::biblio_load(DB::getInstance('mysqli'), $data['biblio_id']);

    // prepare sql statement to insert into biblio_log
    $query = DB::getInstance()->prepare("INSERT INTO biblio_log (biblio_id, user_id, realname, title, ip, action, affectedrow, rawdata, additional_information, date) 
        VALUES (:biblio_id, :user_id, :realname, :title, :ip, :action, :affectedrow, :rawdata, :additional_information, :date)");

    $query->bindValue(':biblio_id', $data['biblio_id'], PDO::PARAM_INT);
    $query->bindValue(':user_id', $_SESSION['uid'], PDO::PARAM_INT);
    $query->bindValue(':realname', $_SESSION['realname']);
    $query->bindValue(':title', $data['title']);
    $query->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
    $query->bindValue(':action', 'update');
    $query->bindValue(':rawdata', urlencode(serialize($old_data)));
    $query->bindValue(':date', date('Y-m-d H:i:s'));

    // compare data
    foreach ($old_data as $key => $datum) {

        // Ignored list, this data is not valid for comparison or has been handled in other methods
        if (in_array($key, ['last_update', 'input_date', 'hash', 'subjects', 'image', 'authors', 'uid', 'classification', 'id', '_id', 'biblio_id'])) continue;

        // for now we will ignore array data too
        if (is_array($datum) || is_array($new_data[$key])) continue;

        // hashing data
        $old_hash = md5($datum);
        $new_hash = md5($new_data[$key]);

        if ($old_hash !== $new_hash) {
            $query->bindValue(':additional_information', 'Change data from "' . $datum . '" to "' . $new_data[$key] . '"');
            $query->bindValue(':affectedrow', $key);
            $query->execute();
        }
    }
});

/**
 * Registering hook on bibliography to show biblio datagrid
 */
$this->register('bibliography_alter_content', function(&$alter_mode, &$content) use ($dbs, $sysconf) {
    if (isset($_GET['action']) && $_GET['action'] == 'history') {
        $alter_mode = 'replace';
        ob_start();
        
        $biblioID = utility::filterData('biblioID', 'get', true, true, true);
        $table_spec = 'biblio_log AS bl';
        $criteria = 'bl.biblio_id=' . $biblioID;
        // create datagrid
        $datagrid = new simbio_datagrid();
        $datagrid->setSQLColumn('bl.date AS \'' . __('Date') . '\'',
            'bl.realname AS \'' . __('User Name') . '\'',
            'bl.additional_information AS \'' . __('Additional Information') . '\'');
        $datagrid->modifyColumnContent(2, 'callback{affectedDetail}');
        $datagrid->setSQLorder('bl.biblio_log_id DESC');
        $datagrid->sql_group_by = 'bl.date';
        $datagrid->setSQLCriteria($criteria);
        // set table and table header attributes
        $datagrid->table_attr = 'id="dataList" class="s-table table"';
        $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
    
        /**
         * 
         * Hook: bibliography_alter_biblio_log_datagrid
         * This hook is used to run plugins code which modify biblio log datagrid
         * before it is printed to the screen
         * 
         * Example usage in plugin code:
         * // pass all function params as reference to modify the value directly
         * $this->register('bibliography_alter_biblio_log_datagrid', function(&$datagrid) use ($dbs, $sysconf) {
         *   global $sysconf;
         *   // add CSS class to the datagrid table
         *   $datagrid->table_attr = 'id="dataList" class="s-table table plugin-table"';
         * });.
         * 
         * @param $datagrid The datagrid object.
         * 
         */
        $this->execute('bibliography_alter_biblio_log_datagrid', [$datagrid]);
    
        // plugins may define this callback function early
        if (!function_exists('affectedDetail')) {
            function affectedDetail($obj_db, $array_data)
            {
                $_q = $obj_db->query("SELECT action,affectedrow,title,additional_information FROM biblio_log WHERE `date` LIKE '" . $array_data[0] . "'");
                $str = '';
                $title = '';
                if ($_q->num_rows > 0) {
                    while ($_data = $_q->fetch_assoc()) {
                        $title = $_data['title'];
                        $str .= ' - ' . $_data['action'] . ' ' . $_data['affectedrow'] . ' : <i>' . $_data['additional_information'] . '</i><br/>';
                    }
                }
                return $title . '</br>' . $str;
            }
        }
    
        // put the result into variables
        $datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, false);
    
        $_q = $dbs->query("SELECT title FROM biblio WHERE biblio_id=" . $biblioID);
        if ($_q->num_rows > 0) {
            $_d = $_q->fetch_row();
            echo '<div class="infoBox">' . __('Biblio Log') . ' : <strong>' . $_d[0] . '</strong></div>';
        }
    
        echo $datagrid_result;
        $content = ob_get_clean();
    }
});

/**
 * Registering hook on bibliography to add Log button for Item Detail
 */
$this->register('bibliography_custom_field_form', function(&$form, &$js, $data) use ($dbs, $sysconf) {
    if ($data && $data['biblio_id']) {
        $form->addCustomBtn('history', __('Log'), $_SERVER['PHP_SELF'] . '?action=history&ajaxLoad=true&biblioID=' . $data['biblio_id'], ' class="s-btn btn btn-success"');
    }
});