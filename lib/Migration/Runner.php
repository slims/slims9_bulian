<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 13/03/2021 8:48
 * @File name           : Runner.php
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

namespace SLiMS\Migration;


class Runner
{
    private string $path;
    private int $version;

    static function path(string $path): Runner
    {
        return new static($path);
    }

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @param int $version
     * @return Runner
     */
    public function setVersion(int $version): Runner
    {
        $this->version = $version;
        return $this;
    }

    private function getMigrationFiles()
    {
        $all_files = scandir(dirname($this->path) . DIRECTORY_SEPARATOR . 'migration');
        $files = array_diff($all_files, ['.','..']);
        natsort($files);
        return $files;
    }

    private function requireFiles($files) {
        foreach ($files as $file) {
            require_once dirname($this->path) . DIRECTORY_SEPARATOR . 'migration' . DIRECTORY_SEPARATOR . $file;
        }
    }

    private function resolve($file)
    {
        $name = str_replace('.php', '', basename($file));
        $className = implode('_', array_slice(explode('_', $name), 1));

        return new $className;
    }

    public function runUp(): int
    {
        $this->requireFiles($files = $this->getMigrationFiles());

        $version = 0;
        foreach ($files as $file) {
            $arr = explode('_', basename($file));
            if (intval($arr[0]) > $this->getVersion()) {
                $instance = $this->resolve($file);
                $instance->up();
                $version = intval($arr[0]);
            }
        }

        if ($this->getVersion() > $version) $version = $this->getVersion();

        return $version;
    }

    public function runDown(): int
    {
        $this->requireFiles($files = array_reverse($this->getMigrationFiles(), false));

        $version = $this->getVersion();
        foreach ($files as $file) {
            $arr = explode('_', basename($file));
            if (intval($arr[0]) <= $this->getVersion()) {
                $instance = $this->resolve($file);
                $instance->down();
                $version = intval($arr[0]);
            }
        }

        return $version;
    }
}