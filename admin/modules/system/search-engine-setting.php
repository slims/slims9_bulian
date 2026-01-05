<?php

/**
 * @author              : Waris Agung Widodo
 * @Date                : 03/01/2025 18:33
 * @Last Modified by    : ido
 * @Last Modified time  : 03/01/2025 18:33
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

use SLiMS\SearchEngine\DefaultEngine;
use SLiMS\SearchEngine\FuzzySearchEngine;
use SLiMS\SearchEngine\SearchBiblioEngine;
use SLiMS\SearchEngine\SphinxSearchEngine;

/* Biblio Index Management section */

// key to authenticate
define('INDEX_AUTH', '1');
// key to get full database access
define('DB_ACCESS', 'fa');

// main system configuration
require '../../../sysconfig.inc.php';

// IP based access limitation
require LIB . 'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');

// start the session
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';
require SIMBIO . 'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';

// privileges checking
$can_read = utility::havePrivilege('bibliography', 'r');
$can_write = utility::havePrivilege('bibliography', 'w');
$error = array();
$info = array();

if (!$can_read) {
    die('<div class="errorBox">' . __('You don\'t have enough privileges to view this section') . '</div>');
}

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['PHP_SELF'], 'post');
$form->submit_button_attr = 'name="updateData" value="' . __('Save Settings') . '" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

// load settings from database
utility::loadSettings($dbs);

// Helper function to safely add or update settings
if (!function_exists('addOrUpdateSetting')) {
    function addOrUpdateSetting($name, $value)
    {
        global $dbs;
        $sql_op = new simbio_dbop($dbs);
        $name = $dbs->real_escape_string($name);
        $data['setting_value'] = $dbs->real_escape_string(serialize($value));

        $query = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = '{$name}'");
        if ($query->num_rows > 0) {
            // update
            $sql_op->update('setting', $data, "setting_name='{$name}'");
        } else {
            // insert
            $data['setting_name'] = $name;
            $sql_op->insert('setting', $data);
        }
    }
}

// start the output buffer
ob_start();

// Get current engine from URL parameter
$engine = isset($_GET['engine']) ? urldecode($_GET['engine']) : '';

// Handle form submission for FuzzySearchEngine settings
if (isset($_POST['updateData'])) {
    if (!$can_write) {
        die('<div class="errorBox">' . __('You don\'t have enough privileges to edit this section') . '</div>');
    }

    // Validate CSRF token if available
    if (!simbio_form_maker::isTokenValid()) {
        $error[] = __('Security token mismatch');
    }

    if (empty($error)) {
        if ($engine === FuzzySearchEngine::class) {
            // Sanitize and validate Fuzzy Search Engine settings
            $fuzzy_config = [];

            // Max Levenshtein distance (1-5)
            $maxDistance = intval($_POST['fuzzy_max_distance'] ?? 2);
            $fuzzy_config['maxDistance'] = ($maxDistance >= 1 && $maxDistance <= 5) ? $maxDistance : 2;

            // Min word length (1-10)
            $minWordLength = intval($_POST['fuzzy_min_word_length'] ?? 3);
            $fuzzy_config['minWordLength'] = ($minWordLength >= 1 && $minWordLength <= 10) ? $minWordLength : 3;

            // Use phonetic matching (boolean)
            $fuzzy_config['usePhonetic'] = isset($_POST['fuzzy_use_phonetic']) ? true : false;

            // Return all if empty (boolean)
            $fuzzy_config['returnAllIfEmpty'] = isset($_POST['fuzzy_return_all_if_empty']) ? true : false;

            // Fallback engine for empty keywords
            $fuzzy_config['fallbackEngine'] = utility::filterData('fallback_engine', 'post') ?? SearchBiblioEngine::class;

            // Save settings safely
            addOrUpdateSetting('fuzzy_search_config', $fuzzy_config);

            // Write log
            writeLog('staff', $_SESSION['uid'], 'system', $_SESSION['realname'] . ' updated Fuzzy Search Engine settings', 'Search Engine Config', 'Update');

            $info[] = __('Fuzzy Search Engine settings saved successfully');
        } else if ($engine === SphinxSearchEngine::class) {
            // Sanitize and validate Sphinx Search Engine settings
            $sphinx_config = [];

            // Sphinx host
            $sphinx_config['host'] = utility::filterData('sphinx_host', 'post') ?? 'localhost';

            // Sphinx port
            $sphinx_port = intval(utility::filterData('sphinx_port', 'post') ?? 9312);
            $sphinx_config['port'] = ($sphinx_port > 0 && $sphinx_port <= 65535) ? $sphinx_port : 9312;

            // Sphinx index name
            $sphinx_config['index_name'] = utility::filterData('sphinx_index', 'post') ?? 'slims';
            // Save settings safely
            addOrUpdateSetting('sphinx_search_config', $sphinx_config);

            // Write log
            writeLog('staff', $_SESSION['uid'], 'system', $_SESSION['realname'] . ' updated Sphinx Search Engine settings', 'Search Engine Config', 'Update');

            $info[] = __('Sphinx Search Engine settings saved successfully');
        }
        utility::jsToastr(__('Search Engine Settings'), __('Settings saved. Refreshing page'), 'success');
        // echo '<script type="text/javascript">setTimeout(() => { top.location.reload(); }, 2000);</script>';
        exit();
    }
}

// Initialize form
$form = new simbio_form_table_AJAX('fuzzySearchForm', $_SERVER['PHP_SELF'] . '?engine=' . urlencode($engine), 'post');
$form->submit_button_attr = 'name="updateData" value="' . __('Save Settings') . '" class="btn btn-default"';
$form->table_attr = 'id="fuzzySearchList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

// Check if viewing Fuzzy Search Engine settings
if ($engine === FuzzySearchEngine::class) {
    // Show errors if any
    if (!empty($error)) {
        foreach ($error as $err) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($err) . '</div>';
        }
    }

    // Show info if any
    if (!empty($info)) {
        foreach ($info as $inf) {
            echo '<div class="alert alert-info">' . htmlspecialchars($inf) . '</div>';
        }
    }

    // Load current Fuzzy Search Engine settings
    $fuzzy_config = config('fuzzy_search_config') ?? [];
    $fuzzy_config = is_array($fuzzy_config) ? $fuzzy_config : [
        'maxDistance' => 2,
        'minWordLength' => 3,
        'usePhonetic' => true,
        'returnAllIfEmpty' => true,
        'fallbackEngine' => SearchBiblioEngine::class
    ];


    // Fuzzy Search Engine Settings Form
    // Max Levenshtein Distance (1-5)
    $form->addTextField('text', 'fuzzy_max_distance', __('Max Levenshtein Distance'), $fuzzy_config['maxDistance'] ?? 2, 'class="form-control col-2" type="number" min="1" max="5" required', __('Maximum number of character differences allowed for fuzzy matching. Value 1 = strict (only nearly perfect matches), value 5 = loose (matches very different words)'));

    // Min Word Length (1-10)
    $form->addTextField('text', 'fuzzy_min_word_length', __('Minimum Word Length'), $fuzzy_config['minWordLength'] ?? 3, 'class="form-control col-2" type="number" min="1" max="10" required', __('Minimum word length to apply fuzzy matching. Words shorter than this value will be matched exactly'));
    // Use Phonetic Matching
    $phonetic_options[] = array('1', __('Enable'));
    $form->addCheckBox('fuzzy_use_phonetic', __('Use Phonetic Matching (Soundex/Metaphone)'), $phonetic_options, $fuzzy_config['usePhonetic'] ?? true, __('Use phonetic matching to capture words that sound similar but are spelled differently, improving search result relevance'));

    // Return All if Empty
    $return_all_options[] = array('1', __('Yes'));
    $form->addCheckBox('fuzzy_return_all_if_empty', __('Return All Documents When Keywords is Empty'), $return_all_options, $fuzzy_config['returnAllIfEmpty'] ?? true, __('If enabled, the system will display all documents when users perform a search without keywords'));

    // select fallback engine class if empty keywords
    $form->addSelectList('fallback_engine', __('Fallback Engine for Empty Keywords'), [
        DefaultEngine::class => DefaultEngine::class,
        SearchBiblioEngine::class => SearchBiblioEngine::class
    ], $fuzzy_config['fallbackEngine'] ?? SearchBiblioEngine::class, 'class="form-control"', __('Select an alternative search engine to use when keywords are empty'));


    // Print form
    echo $form->printOut();
} else if ($engine === SphinxSearchEngine::class) {
    // load current Sphinx Search Engine settings
    $sphinx_config = config('sphinx_search_config') ?? [];
    $sphinx_config = is_array($sphinx_config) ? $sphinx_config : [
        'host' => 'localhost',
        'port' => 9312,
        'index_name' => 'slims'
    ];
    // Sphinx Search Engine settings form
    $form->addTextField('text', 'sphinx_host', __('Sphinx Host'), $sphinx_config['host'] ?? 'localhost', 'class="form-control col-4" required', __('Hostname or IP address of the Sphinx search server'));
    $form->addTextField('text', 'sphinx_port', __('Sphinx Port'), $sphinx_config['port'] ?? '9312', 'class="form-control col-2" type="number" required', __('Port number of the Sphinx search server'));
    $form->addTextField('text', 'sphinx_index', __('Sphinx Index Name'), $sphinx_config['index_name'] ?? 'slims', 'class="form-control col-4" required', __('Name of the Sphinx index to use for searching bibliography records'));
    // Print form
    echo $form->printOut();
} else {
    echo '<div class="alert alert-warning">' . __('Sorry, this engine is not supported for configuration') . '</div>';
}

/* main content end */
$content = ob_get_clean();
// include the page template
require SB . '/admin/' . $sysconf['admin_template']['dir'] . '/notemplate_page_tpl.php';
