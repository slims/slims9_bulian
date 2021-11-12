<?php
/**
 * Copyright (C) 2008 Arie Nugraha (dicarve@yahoo.com)
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

/* Reporting module submenu items */

// IP based access limitation
do_checkIP('smc');
do_checkIP('smc-reporting');

$menu[] = array('Header', __('REPORTING FORM'));
$menu[] = array(__('Collection Statistic'), MWB.'reporting/index.php', __('View Library Collection Statistic'));
$menu[] = array(__('Loan Report'), MWB.'reporting/loan_report.php', __('View Library Loan Report'));
$menu[] = array(__('Membership Report'), MWB.'reporting/member_report.php', __('View Membership Report'));
$menu[] = array('Header', __('OTHER REPORTS'));
// other/custom report menu
require MDLBS.'reporting/customs/customs_report_list.inc.php';
