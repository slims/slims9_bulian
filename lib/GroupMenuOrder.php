<?php
/*
 * File: GroupMenuOrder.php
 * Project: lib
 * Created Date: Saturday May 28th 2022 9:01:32 pm
 * Author: Waris Agung Widodo (ido.alit@gmail.com)
 * -----
 * Last Modified: Saturday May 28th 2022 9:37:40 pm
 * Modified By: Waris Agung Widodo
 * -----
 * License: GNU GPL v3
 * -----
 * HISTORY:
 * Date      	By	Comments
 * ----------	---	---------------------------------------------------------
 */

namespace SLiMS;

class GroupMenuOrder
{
    private static $instance;
    private array $order = [];
    private ?string $current_group;

    /**
     * Disable constructor
     * @return void 
     */
    private function __construct()
    {
    }

    /**
     * @return GroupMenuOrder
     */
    public static function getInstance(): GroupMenuOrder
    {
        if (is_null(self::$instance)) self::$instance = new static;
        return self::$instance;
    }

    public function bind($current_group)
    {
        $this->current_group = strtolower($current_group);
        return $this;
    }

    public function before($group_name)
    {
        $this->order('before', $group_name);
    }

    public function after($group_name)
    {
        $this->order('after', $group_name);
    }

    public function order($position, $group_name)
    {
        if (!is_null($this->current_group)) {
            $this->order[$this->current_group] = ['position' => $position, 'group' => strtolower($group_name)];
        }
        
        $this->current_group = null;
    }

    public function getOrder()
    {
        return $this->order;
    }
}
