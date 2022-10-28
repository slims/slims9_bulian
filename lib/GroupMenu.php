<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 18/12/2021 11:51
 * @File name           : GroupMenu.php
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

namespace SLiMS;


class GroupMenu
{
    private static $instance;
    private $uniq_id;
    private $group = [];
    private $plugin_in_group = [];

    /**
     * @return GroupMenu
     */
    public static function getInstance(): GroupMenu
    {
        if (is_null(self::$instance)) self::$instance = new static;
        return self::$instance;
    }

    /**
     * @param $uniq_id
     * @return $this
     */
    function bind($uniq_id): GroupMenu
    {
        $this->uniq_id = $uniq_id;
        return $this;
    }

    function group($group_name)
    {
        if (!isset($this->group[strtolower($group_name)])) $this->group[strtolower($group_name)] = [];
        if (!is_null($this->uniq_id) && !in_array($this->uniq_id, $this->group[strtolower($group_name)])) {
            $this->group[strtolower($group_name)][] = $this->uniq_id;
            $this->plugin_in_group[] = $this->uniq_id;
            $this->uniq_id = null;
        }
        return GroupMenuOrder::getInstance()->bind($group_name);
    }

    /**
     * @return array
     */
    public function getGroup(): array
    {
        return $this->group;
    }

    public function getPluginInGroup(): array
    {
        return $this->plugin_in_group;
    }

    function getGroupName($uniq_id)
    {
        foreach ($this->group as $key => $group) {
            if (in_array($uniq_id, $group)) return $key;
        }
        return null;
    }
}