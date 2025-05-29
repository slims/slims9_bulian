<?php
/**
 * Circulation module submenu items.
 * 
 * @author Original code by Ari Nugraha (dicarve@gmail.com).
 * @package SLiMS
 * @subpackage Circulation
 * @since 2007
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License Version 3
 *
 */

// IP based access limitation
do_checkIP('smc');
do_checkIP('smc-circulation');

$menu[] = array('Header', __('CIRCULATION'));
$menu[] = array(__('Start Transaction'), MWB.'circulation/index.php?action=start', __('Start Circulation Transaction Proccess'));
$menu[] = array(__('Quick Return'), MWB.'circulation/quick_return.php', __('Quick Return Collection'));
$menu[] = array(__('Loan Rules'), MWB.'circulation/loan_rules.php', __('View and Modify Circulation Loan Rules'));
$menu[] = array(__('Loan History'), MWB.'reporting/customs/loan_history.php', __('Loan History Overview'));
$menu[] = array(__('Due Date Warning'), MWB.'reporting/customs/due_date_warning.php', __('View Members That About to Have Overdues'));
$menu[] = array(__('Overdued List'), MWB.'reporting/customs/overdued_list.php', __('View Members Having Overdues'));
$menu[] = array(__('Reservation'), MWB.'reporting/customs/reserve_list.php', __('Reservation'));
