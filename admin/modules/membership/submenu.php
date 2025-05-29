<?php
/**
 * Membership module submenu items
 * 
 * @author Original code by Ari Nugraha (dicarve@gmail.com).
 * @package SLiMS
 * @subpackage Membership
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

// IP based access limitation
do_checkIP('smc');
do_checkIP('smc-membership');

$menu[] = array('Header', __('MEMBERSHIP'));
$menu[] = array(__('View Member List'), MWB.'membership/index.php', __('View Library Member List'));
$menu[] = array(__('Add New Member'), MWB.'membership/index.php?action=detail', __('Add New Library Member Data'));
$menu[] = array(__('Member Type'), MWB.'membership/member_type.php', __('View and modify member type'));
$menu[] = array('Header', __('TOOLS'));
$menu[] = array(__('Member Card Printing'), MWB.'membership/member_card_generator.php', __('Print Member Card'));
$menu[] = array(__('Member Data Export'), MWB.'membership/export.php', __('Export Members Data To CSV File'));
$menu[] = array(__('Member Data Import'), MWB.'membership/import.php', __('Import Members Data From CSV File'));
