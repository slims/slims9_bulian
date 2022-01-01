<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 01/01/2022 9:44
 * @File name           : Engine.php
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

namespace SLiMS\SearchEngine;


class Engine
{
    private static $instance;

    protected array $engine = [DefaultEngine::class, SearchBiblioEngine::class];

    private function __construct()
    {
    }

    static function init(): Engine
    {
        if (is_null(self::$instance)) self::$instance = new static;
        return self::$instance;
    }

    function set($class_name): Engine
    {
        $this->engine[] = $class_name;
        return $this;
    }

    function get(): array
    {
        return $this->engine;
    }
}