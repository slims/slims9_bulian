<?php
/**
 * Copyright (C) Ari Nugraha (dicarve@gmail.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

$menu[] = array('Header', __('SHORTCUT'));
$menu['user-profile'] = array(__('Change User Profiles'), MWB.'system/app_user.php?changecurrent=true&action=detail', __('Change Current User Profiles and Password'));
$menu[] = array(__('Shortcut Setting'), MWB.'system/shortcut.php', __('Shortcut Setting'));
$menu[] = array(__('Theme'), MWB.'system/theme.php', __('Configure theme Preferences'));
