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

/* Membership module submenu items */
// IP based access limitation
do_checkIP('smc');
do_checkIP('smc-system');

global $sysconf;

$menu['system.header-configuration'] = array('Header', __('CONFIGURATION'));
// only administrator have privileges for below menus
if ($_SESSION['uid'] == 1) {
    $menu['system.system-configuration'] = array(__('System Configuration'), MWB.'system/index.php', __('Configure Global System Preferences'));
    $menu['system.system-environment'] = array(__('System Environment'), MWB.'system/envinfo.php', __('Information about System Environment'));
    $menu['system.system-environment-setting'] = array(__('System Environment Setting'), MWB.'system/envsetting.php', __('Configure System Environment Mode'));
    $menu['system.ucs-setting'] = array(__('UCS Setting'), MWB.'system/ucsetting.php', __('Configure UCS Preferences'));
    $menu['system.theme'] = array(__('Theme'), MWB.'system/theme.php', __('Configure theme Preferences'));
    $menu['system.plugins'] = array(__('Plugins'), MWB . 'system/plugins.php');
    $menu['system.custom-field'] = array(__('Custom Field'), MWB.'system/custom_field.php', __('Configure custom field'));
    $menu['system.currency-setting'] = array(__('Currency Setting'), MWB.'system/currencysetting.php', __('Configure System Currency'));
    $menu['system.email-setting'] = array(__('E-Mail Setting'), MWB.'system/mailsetting.php', __('Configure E-Mail Preferences'));
    $menu['system.captcha-setting'] = array(__('Captcha Setting'), MWB.'system/captchasetting.php', __('Configure Captcha'));
}
$menu['system.content'] = array(__('Content'), MWB.'system/content.php', __('Content'));
// only administrator have privileges for below menus
if ($_SESSION['uid'] == 1) {
    if ($sysconf['index']['engine']['enable']) {
      $menu['system.biblio-indexes'] = array(__('Biblio Indexes'), MWB.'system/biblio_indexes_'.$sysconf['index']['engine']['type'].'.php', __('Bibliographic Indexes management'));
    } else {
      $menu['system.biblio-indexes'] = array(__('Biblio Indexes'), MWB.'system/biblio_indexes.php', __('Bibliographic Indexes management'));
    }
    $menu['system.modules'] = array(__('Modules'), MWB.'system/module.php', __('Configure Application Modules'));
    $menu['system.user-group'] = array(__('User Group'), MWB.'system/user_group.php', __('Manage Group of Application User'));
    $menu['system.librarian-users'] = array(__('Librarian & System Users'), MWB.'system/app_user.php', __('Manage Application User or Library Staff'));
    $menu['system.image-maintenance'] = array(__('Image Maintenance'), MWB.'system/image_maintenance.php', __('Manage All Image Assets.'));

}
$menu['system.shortcut-setting'] = array(__('Shortcut Setting'), MWB.'system/shortcut.php', __('Shortcut Setting'));
$menu['system.holiday-setting'] = array(__('Holiday Setting'), MWB.'system/holiday.php', __('Configure Holiday Setting'));
$menu['system.barcode-generator'] = array(__('Barcode Generator'), MWB.'system/barcode_generator.php', __('Barcode Generator'));
$menu['system.system-log'] = array(__('System Log'), MWB.'system/sys_log.php', __('View Application System Log'));
$menu['system.database-backup'] = array(__('Database Backup'), MWB.'system/backup.php', __('Backup Application Database'));