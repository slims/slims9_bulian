<?php
/**
 * simbio_date class
 * A Collection of static function for doing date arithmatic related operation
 *
 * Copyright (C) 2010 Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

/**
 * Emulate date_parse function for PHP < 5.2
 */
if (!function_exists('date_parse')) {
    function date_parse($str_date) {
        $_ts = strtotime($str_date);
        $_date = getdate($_ts);
        return array('month' => $_date['mon'], 'day' => $_date['mday'], 'year' => $_date['year']);
    }
}

class simbio_date
{
    /* ALL METHODS DATE ARGUMENT(s) IS ASSUMED USING YYYY-MM-DD format */

    /**
     * Static Method to get next date
     *
     * @param   integer $int_day_num
     * @param   string  $str_start_date
     * @param   string  $str_start_format
     * @return  string
     */
    public static function getNextDate($int_day_num = 1, $str_start_date = '', $str_date_format = 'Y-m-d')
    {
        if ($int_day_num < 1) { return $str_start_date; }
        if (!$str_start_date) {
            return date($str_date_format, mktime(0, 0, 0, intval(date('n')), (intval(date('j'))+$int_day_num), intval(date('Y')) ) );
        } else if ($_parsed_date = date_parse($str_start_date)) {
			$_next_date = date($str_date_format, mktime(0, 0, 0, $_parsed_date['month'], $_parsed_date['day']+$int_day_num, $_parsed_date['year'] ) );
			return $_next_date;
        } else {
            return null;
        }
    }


    /**
     * Static Method to get previous date
     *
     * @param   integer $int_day_num
     * @param   string  $str_start_date
     * @param   string  $str_date_format
     * @return  string
     */
    public static function getPrevDate($int_day_num = 1, $str_start_date = '', $str_date_format = 'Y-m-d')
    {
        if ($int_day_num < 1) { return $str_start_date; }
        if (!$str_start_date) {
            return date($str_date_format, mktime(0, 0, 0, intval(date('n')), (intval(date('j'))-$int_day_num), intval(date('Y')) ) );
        } else if ($_parsed_date = @date_parse($str_start_date)) {
            return date($str_date_format, mktime(0, 0, 0, $_parsed_date['month'], $_parsed_date['day']-$int_day_num, $_parsed_date['year'] ) );
        } else {
            return null;
        }
    }


    /**
     * Static Method to get number of day between dates
     *
     * @param   string  $str_start_date
     * @param   string  $str_end_date
     * @return  integer
     */
    public static function calcDay($str_start_date, $str_end_date)
    {
        $_parsed_start_date = @date_parse($str_start_date);
        $_parsed_end_date = @date_parse($str_end_date);
        $_start_mktime = mktime(0, 0, 0, $_parsed_start_date['month'], $_parsed_start_date['day'], $_parsed_start_date['year']);
        $_end_mktime = mktime(0, 0, 0, $_parsed_end_date['month'], $_parsed_end_date['day'], $_parsed_end_date['year']);
        $_mksec = $_end_mktime-$_start_mktime;
        return abs(intval(round($_mksec/(3600*24))));
    }


    /**
     * Static Method to get number of holiday between dates
     *
     * @param   string  $str_start_date
     * @param   string  $str_end_date
     * @param   array   $array_holiday_name
     * @param   array   $array_holiday_date
     * @return  integer
     */
    public static function countHolidayBetween($str_start_date, $str_end_date, $array_holiday_dayname = array('Sun'), $array_holiday_date = array())
    {
        $_holiday_count = 0;
        $_one_day = 3600*24;
        $_parsed_start_date = @date_parse($str_start_date);
        $_parsed_end_date = @date_parse($str_end_date);
        $_start_mktime = mktime(0, 0, 0, $_parsed_start_date['month'], $_parsed_start_date['day'], $_parsed_start_date['year']);
        $_end_mktime = mktime(0, 0, 0, $_parsed_end_date['month'], $_parsed_end_date['day'], $_parsed_end_date['year']);
        while ($_start_mktime <= $_end_mktime) {
            if (in_array(strtolower(date('D', $_start_mktime)), $array_holiday_dayname) OR in_array(date('Y-m-d', $_start_mktime), $array_holiday_date)) {
                $_holiday_count += 1;
            }
            $_start_mktime += $_one_day;
        }

        return $_holiday_count;
    }


    /**
     * Static Method to compare dates and return the latest date
     *
     * @param   string  $str_date_to_compares
     * @return  string
     */
    public static function compareDates()
    {
        if (func_num_args() < 1) {
            return null;
        } else if (func_num_args() == 2) {
            // get value of method arguments
            $date1 = func_get_arg(0);
            $date2 = func_get_arg(1);
            // check if $date1 and $date2 is same
            if ($date1 == $date2) {
                return null;
            }
            // get the UNIX timestamp of date
            $_parsed_date1 = date_parse($date1);
            $_parsed_date2 = date_parse($date2);
            $timestamp1 = mktime(0, 0, 0, $_parsed_date1['month'], $_parsed_date1['day'], $_parsed_date1['year']);
            $timestamp2 = mktime(0, 0, 0, $_parsed_date2['month'], $_parsed_date2['day'], $_parsed_date2['year']);
            if ($timestamp1 > $timestamp2) {
                return $date1;
            } else {
                return $date2;
            }
        }

        $func_args = func_get_args();
        $latest = func_get_arg(0);
        foreach ($func_args as $args) {
            $latest = self::compareDates($latest, $args);
        }

        return $latest;
    }


    /**
     * Static Method to get next date that are not holidays
     *
     * @param   string  $str_date
     * @param   array   $array_holiday_dayname
     * @param   array   $array_holiday_date
     * @return  string
     */
    public static function getNextDateNotHoliday($str_date, $array_holiday_dayname = array(), $array_holiday_date = array())
    {

    // if array dayname and date is empty
        if (!$array_holiday_dayname AND !$array_holiday_date) {
            return $str_date;
        }

        // check date array first
        $d = false;
		$_str_date_next = $str_date;
		if ($array_holiday_date) {
            foreach ($array_holiday_date as $_idx=>$_each_date) {
                if ($str_date == $_each_date) { $d = true; }
            }
        }
		if ($d) {
			$_str_date_next = self::getNextDate(1, $_str_date_next);
		}

        // parse date
        $_parsed_date = date_parse($_str_date_next);
        // get dayname of $str_date
        $dayname = date('D', mktime(0, 0, 0, $_parsed_date['month'], $_parsed_date['day'], $_parsed_date['year']));
		$n = false;
		// check dayname
        if (in_array(strtolower($dayname), $array_holiday_dayname)) {
			$n = true;
            $_str_date_next = self::getNextDate(1, $_str_date_next);
        }

        //looping break
        if (!$d and !$n) {
            return $_str_date_next;
        } else {
            return self::getNextDateNotHoliday($_str_date_next, $array_holiday_dayname, $array_holiday_date);
        }
    }


    /**
     * Generate calendar
     *
     * @param   mixed   $mix_year: year
     * @param   mixed   $mix_month: month
     * @param   array   $arr_date_data: optional date data
     * @return  string
     */
    public static function generateCalendar($mix_year, $mix_month, $arr_date_data = array())
    {
        $_calendar = '<table cellspacing="0" class="calendar">'."\n";
        $_date = getdate(strtotime($mix_year.'-'.$mix_month.'-01'));
        $_max_week = 5;

        // start day of month
        $_start_day = $_date['wday'];
        if ($_start_day == 6) {
            $_max_week = 6;
        }
        // get the last date of month
        $_lastdate_ts = mktime(0, 0, 0, preg_replace('@^0+@i', '', $mix_month)+1, 0, (integer)$mix_year);
        $_lastdate =  date('j', $_lastdate_ts);

        $_day[0] = __('Sunday');
        $_day[1] = __('Monday');
        $_day[2] = __('Tuesday');
        $_day[3] = __('Wednesday');
        $_day[4] = __('Thursday');
        $_day[5] = __('Friday');
        $_day[6] = __('Saturday');

        // calendar table head
        $_calendar .= '<tr class="week">';
        foreach ($_day as $_wday => $_dayname) {
            $_calendar .= '<th class="dayname">'.$_dayname.'</th>';
        }
        $_calendar .= '</tr>'."\n";

        $_week_alter = 'even';
        for ($_w = 1; $_w <= $_max_week; $_w++) {
            $_week_alter = ($_w%2)?'even':'odd';
            $_calendar .= '<tr class="week">';
            foreach ($_day as $_wday => $_dayname) {
                if ($_w == 1 && $_wday == $_start_day ) {
                    $_mday = 1;
                    $_date_data = (isset($arr_date_data[$_mday]))?$arr_date_data[$_mday]:'';
                    $_calendar .= '<td class="day '.$_week_alter.'"><div class="day_number">'.$_mday.$_date_data.'</div></td>';
                } else if (isset($_mday) && $_mday < $_lastdate) {
                    $_mday++;
                    $_date_data = (isset($arr_date_data[$_mday]))?$arr_date_data[$_mday]:'';
                    $_calendar .= '<td class="day '.$_week_alter.'"><div class="day_number">'.$_mday.$_date_data.'</div></td>';
                } else {
                    $_calendar .= '<td class="day '.$_week_alter.' none"><div class="day_number">&nbsp;</div></td>';
                }
            }
            $_calendar .= '</tr>'."\n";
        }
        $_calendar .= '</table>'."\n";

        return $_calendar;
    }
}
