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
use SLiMS\Plugins;

/**
 * Get plugin instance
 */
$plugin = Plugins::getInstance();

/**
 *  Prepare variable to storing old data
 */
$old_data = [];

/**
 * Registering hook plugin on bibliography before updated
 * In this hook, we will get the old data.
 */
$plugin->register('bibliography_before_update', function ($data) use (&$old_data) {
    // api still uses mysqli driver, so we use the mysqli instance instead of pdo
    $old_data = api::biblio_load(DB::getInstance('mysqli'), $data['biblio_id']);
});

/**
 * Registering hook on bibliography after updated
 * In this hook, we will compare old data with new data per field
 */
$plugin->register('bibliography_after_update', function ($data) use (&$old_data) {

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