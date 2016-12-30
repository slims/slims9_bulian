<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-15 18:47:55
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-22 19:39:32
 */

echo biblioSimpleList('ORDER BY input_date DESC', $sysconf['template']['new-books'], __('New Books'));