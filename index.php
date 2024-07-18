<?php
/**
 * SENAYAN application bootstrap files
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * Some modifications & patches by Hendro Wicaksono (hendrowicaksono@yahoo.com)
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

use SLiMS\{Opac,Plugins};

// key to authenticate
define('INDEX_AUTH', '1');

// required file
require 'sysconfig.inc.php';

// Cleanup SQL Injection and Common XSS
$sanitizer->cleanUp(exception: ['contentDesc','fieldEnc']);

// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('opac');

// member session params
require LIB.'member_session.inc.php';
if ($sysconf['template']['base'] == 'html') {
  require SIMBIO.'simbio_GUI/template_parser/simbio_template_parser.inc.php';
}

// default opac variable
$opacVariable = [
    // default library info
    'page_title' => $sysconf['library_subname'].' | '.$sysconf['library_name'],

    // total opac result page
    'info' => __('Web Online Public Access Catalog - Use the search options to find documents quickly'),

    // total opac result page
    'total_pages' => 1,

    // default header info
    'header_info' => (utility::isMemberLogin() ? '<div class="alert alert-info alert-member-login" id="memberLoginInfo">'.__('You are currently Logged on as member').': <strong>'.$_SESSION['m_name'].' (<em>'.$_SESSION['m_email'].'</em>)</strong> <a id="memberLogout" href="index.php?p=member&logout=1">'.__('LOGOUT').'</a></div>' : ''),

    // HTML metadata
    'metadata' => '',

    // JS
    'js' => '',

    // searched words for javascript highlight
    'searched_words_js_array' => '',
    'available_languages' => $localisation->getLanguages(),

    // Sanitizer
    'sanitizer' => $sanitizer,
];

// OPAC Instance
$opacClass = config('custom_opac', $slimsOpac = Opac::class);
$opac = new $opacClass($opacVariable, $sysconf, $dbs);

if (!$opac instanceof Opac) {
  throw new Exception("{$opacClass} is not instance of {$slimsOpac}");
}

// running hook to override process/variable before
// content load
$opac->hookBeforeContent(function($opac){
  // Set header for CSP
  $opac->setCsp();
  $opac->setHeader('X-Content-Type-Options', 'nonsniff');
  
  // running plugin based on hook
  Plugins::getInstance()->execute(Plugins::CONTENT_BEFORE_LOAD, [$opac]);
});

// Path process or show welcome page
$opac->onWeb(function($opac){
  $opac->handle('p')->orWelcome();
})->onCli();

// running hook to override process/variable after
// content load
$opac->hookAfterContent(function($opac){
  Plugins::getInstance()->execute(Plugins::CONTENT_AFTER_LOAD, [$opac]);
});

// templating
$opac->parseToTemplate();