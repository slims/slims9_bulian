<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 14/03/2021 18:03
 * @File name           : 1_CreateReadCounterTable.php
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

class CreateReadCounterTable extends \SLiMS\Migration\Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    function up()
    {
        \SLiMS\DB::getInstance()->query("CREATE TABLE `read_counter` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `item_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
          `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    function down()
    {
        \SLiMS\DB::getInstance()->query("DROP TABLE `read_counter`;");
    }
}