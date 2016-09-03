<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-15 16:21:17
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-22 19:36:49
 */

echo biblioSimpleList('WHERE promoted=1 ORDER BY last_update DESC', $sysconf['template']['promoted-books'], __('Promoted Books'));