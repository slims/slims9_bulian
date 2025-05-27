<?php
/**
 * Plugin Name: Biblio Plugin Example.
 * Plugin URI: https://github.com/slims/slims9_bulian
 * Description: This plugin provides examples on how to use hooks for bibliography module.
 * Version: 1.0.0
 * Author: Ari Nugraha
 * Author URI: https://github.com/dicarve
 */

/**
 * Register plugin's CSS and JS
 */
$this->registerCSS(PWB . 'biblio_plugin/style.css', 'admin');
$this->registerJS( PWB . 'biblio_plugin/script.js', 'admin');

/**
 * This hook is executed very early in bibliographic module
 * to allow for some initialization tasks
 * 
 * @param array $data the data from form submission
 */
$this->register('bibliography_init', function() use ($dbs, $sysconf) {
  // do something for initialization phase here
  // such as writing log on user access, print some message, etc.
  utility::jsToastr(__('Biblio Init'), __('You are accessing bibliography section'), 'success'); 
});

/**
 * This hook is executed when the custom field data is submitted
 * 
 * @param array $custom_data the custom field data from form submission
 */
$this->register('bibliography_alter_custom_field_data', function(&$custom_data) use ($dbs, $sysconf) {
  // empty the custom field data
  $custom_data = [];
});

/**
 * This hook is executed prior to form validation
 * 
 * @param array  $data The form data.
 * @param bool   $validation the validation flag, set it false to make the form invalid and cancel submission
 * @param string $invalid_msg the message shown when the form is invalid
 */
$this->register('bibliography_form_data_validation', function(&$data, &$validation, &$invalid_msg) use ($dbs, $sysconf) {
  // check if the classification start with number
  if (!preg_match("/^(\d+).*/i")) {
    $validation = false;
    $invalid_msg = 'Only decimal classification allowed!';
  }
});

/**
 * This hook is executed prior to bibliographic form data inserted into database
 * 
 * @param array $data the data from form submission
 */
$this->register('bibliography_before_save', function(&$data) use ($dbs, $sysconf) {
  // modify title field data by making all characters uppercase
  $data['title'] = strtoupper($data['title']);
});

/**
 * This hook is executed after the bibliographic data successfully inserted to database
 * 
 * @param array $data the data from form submission
 */
$this->register('bibliography_after_save', function ($data) use ($dbs, $sysconf) {

});

/**
 * This hook is executed prior to bibliographic form data updated into database
 * 
 * @param array $data the data from form submission
 */
$this->register('bibliography_before_update', function (&$data) use ($dbs, $sysconf) {

});

/**
 * This hook is executed after the bibliographic form data successfully updated to database
 * 
 * @param array $data the data from form submission
 */
$this->register('bibliography_after_update', function ($data) use ($dbs, $sysconf) {

});

/**
 * This hook is executed when the datagrid items (biblio IDs) submitted
 * 
 * @param array $id_array the array containing biblio IDs
 */
$this->register('bibliography_preprocess_datagrid_items', function(&$id_array) use ($dbs, $sysconf) {
    // do something with the array such as looping through each ID and modify it
    foreach ($id_array as $biblio_id) {
        // do something for each $biblio_id
    }
});

/**
 * This hook is executed prior to deletion of item
 * 
 * @param integer $id the biblio ID
 */
$this->register('bibliography_before_delete', function(&$id) use ($dbs, $sysconf) {
    // do something before the data deleted
    // such as preventing this particular ID of biblio to be removed by altering the value to 0
    $id = 0;
});

/**
 * This hook is executed after to biblio data successfully deleted
 * 
 * @param integer $id the biblio ID
 */
$this->register('bibliography_after_delete', function($id) use ($dbs, $sysconf) {
    // do something after the data deleted
});

/**
  * This hook is executed to modify the search form header
  * before it is printed to the screen
  * 
  * @param string $form_header The HTML string of search form header.
  * 
  */
$this->register('bibliography_alter_form_header', function(&$form_header) use ($dbs, $sysconf) {
  // add quick links
  $form_header .= '<div class="alert alert-warning">Subject/Topic Quick Search: '
    .'<a href="'.MWB.'bibliography/index.php?keywords=Programming&field=subject&opac_hide=&promoted=" class="btn btn-primary">Programming</a>&nbsp;'
    .'<a href="'.MWB.'bibliography/index.php?keywords=Database&field=subject&opac_hide=&promoted=" class="btn btn-primary">Database</a>&nbsp;'
    .'<a href="'.MWB.'bibliography/index.php?keywords=Open+Source&field=subject&opac_hide=&promoted=" class="btn btn-primary">Open Source</a>&nbsp;'
    .'<a href="'.MWB.'bibliography/index.php?keywords=Corruption&field=subject&opac_hide=&promoted=" class="btn btn-warning">Corruption</a>&nbsp;'
    .'<a href="'.MWB.'bibliography/index.php?keywords=Poverty&field=subject&opac_hide=&promoted=" class="btn btn-warning">Poverty</a>&nbsp;'
    .'</div>';
});

/**
 * This hook is executed on bibliography main content
 * You can add new content or replacing the main page content
 * 
 * @param string $alter_mode the value can be: 'add' to add the content, or 'replace' to replace the main content entirely
 * @param string $content the new content
 */
$this->register('bibliography_alter_content', function(&$alter_mode, &$content) use ($dbs, $sysconf) {
    $alter_mode = 'add';
    ob_start();
    ?>
    <div class="ai-main-container">
        <div class="ai-prompt-container">
            <div class="ai-prompt-header">Bibliography AI Prompt</div>
            <textarea class="ai-prompt-input" placeholder="Ask AI about bibliographic metadata here..."></textarea>
            <div class="model-selection">
                <select id="ai-model" class="form-select">
                    <option value="gpt3">GPT-3</option>
                    <option value="gpt4">GPT-4</option>
                    <option value="claude">Claude</option>
                    <option value="deepseek">Deepseek</option>
                    <option value="llama">Llama</option>
                </select>
                &nbsp;<button class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
});

/**
 * This hook is executed before the bibliographic form printed out to the screen
 * 
 * @param object $form the simbio form object
 * @param string $js custom javascript string
 * @param array  $data the custom field data array 
 */
$this->register('bibliography_custom_field_form', function(&$form, &$js, $data) use ($dbs, $sysconf) {
    $form->addAnything(__('Custom Element'), 'This is custom element from Biblio Plugin', 'custom_element');
});