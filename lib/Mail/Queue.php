<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-10-07 00:41:13
 * @modify date 2022-10-07 00:52:03
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Mail;

use Closure;

trait Queue
{
    /**
     * For NSQ|Redis|Etc
     *
     * @param Closure $callback
     * @return void
     */
    public function setQueue(Closure $callback)
    {
        $callback($this);
    }
}