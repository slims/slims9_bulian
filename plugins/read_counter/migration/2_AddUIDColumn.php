<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 17/03/2021 18:56
 * @File name           : 2_AddUIDColumn.php
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

class AddUIDColumn extends \SLiMS\Migration\Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    function up()
    {
        \SLiMS\DB::getInstance()->query("ALTER TABLE read_counter ADD COLUMN uid int(11) NULL AFTER created_at;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    function down()
    {
        \SLiMS\DB::getInstance()->query("ALTER TABLE read_counter DROP COLUMN uid;");
    }
}