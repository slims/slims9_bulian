<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-12-04 07:48:19
 * @modify date 2022-12-04 07:59:24
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Session\Driver;

abstract class Contract
{
    abstract public function admin();
    abstract public function memberArea();
}