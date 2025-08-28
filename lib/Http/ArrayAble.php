<?php
/**
 * @author Drajat Hasan
 * @email drajathasan20@gmail.com
 * @create date 2022-11-15 08:58:49
 * @modify date 2022-11-15 17:10:08
 * @license GPLv3
 * @desc [description]
 */

namespace SLiMS\Http;
use ArrayIterator;

trait ArrayAble
{
   /**
     * Implement IteratorAggregate interface
     *
     * Use for iterate attributes at foreach()
     * 
     * @param Undocumented function value
     * @return value
     */
    public function getIterator(): ArrayIterator
    {
        $data = $this->toJson()->toArray();
        return new ArrayIterator(is_array($data) ? $data : []);
    }     
}
