<?php
/**
 * simbio_paging_ajax
 * Paging Generator class - Enhanced Ultimate Edition
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 * GPL v2 or later
 */

// Pastikan file tidak diakses langsung
if (!defined('INDEX_AUTH') || INDEX_AUTH != 1) { 
    die("can not access this file directly");
}

class simbio_paging
{
    /**
     * Static Method to print out the paging list
     *
     * @param   integer $int_all_recs_num   Total semua baris data
     * @param   integer $int_recs_each_page Jumlah data per halaman
     * @param   integer $int_pages_each_set Jumlah angka link yang tampil (default 5 untuk sliding)
     * @param   string  $str_fragment       Anchor fragment (e.g., #section)
     * @param   string  $str_target_frame   Target atribut link
     * @return  string
     */
    public static function paging($int_all_recs_num, $int_recs_each_page, $int_pages_each_set = 5, $str_fragment = '', $str_target_frame = '_self')
    {
        // 1. Cek argumen dasar yang tidak valid
        if ($int_recs_each_page >= $int_all_recs_num || $int_recs_each_page <= 0) {
            return '';
        }

        // 2. Hitung total halaman
        $_num_page_total = (int)ceil($int_all_recs_num / $int_recs_each_page);
        if ($_num_page_total < 2) {
            return '';
        }

        // 3. Ambil dan validasi halaman saat ini (Mencegah Out of Bounds)
        $_page = 1;
        if (isset($_GET['page'])) {
            $_page = (int)$_GET['page'];
            if ($_page < 1) {
                $_page = 1;
            } elseif ($_page > $_num_page_total) {
                $_page = $_num_page_total; // Paksa ke halaman maksimum jika input ngawur
            }
        }

        // 4. Bersihkan dan bangun Query String (Anti-XSS & Support Array Parameters)
        $_query_str_page = '';
        if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
            parse_str($_SERVER['QUERY_STRING'], $arr_query_var);
            
            // Perbarui token CSRF jika ada di query string
            if (isset($arr_query_var['csrf_token'])) {
                $arr_query_var['csrf_token'] = $_SESSION['csrf_token'] ?? '';
            }
            
            // Singkirkan parameter page lama agar tidak duplikat
            unset($arr_query_var['page']);

            // Bersihkan XSS secara aman menggunakan helper rekursif
            $cleaned_query = self::cleanQueryArray($arr_query_var);
            
            if (!empty($cleaned_query)) {
                $_query_str_page = http_build_query($cleaned_query) . '&';
            }
        }
        
        $_query_str_page .= 'page=';
        $_current_page = $_SERVER['PHP_SELF'] . '?' . $_query_str_page;

        // Atribut target frame aman
        $str_target_frame = 'target="' . htmlspecialchars($str_target_frame, ENT_QUOTES, 'UTF-8') . '"';

        // Inisialisasi Buffer HTML
        $_buffer = '<div class="paging-container"><span class="pagingList">';

        // LOKALISASI TEKS (Simbio/SLiMS internal function)
        $_first = __('First Page');
        $_prev  = __('Previous');
        $_next  = __('Next');
        $_last  = __('Last Page');

        // 5. LINK FIRST & PREVIOUS
        if ($_page > 1) {
            $_buffer .= '<a href="' . $_current_page . '1' . $str_fragment . '" ' . $str_target_frame . ' class="page_link first_link">' . $_first . '</a>' . "\n";
            $_buffer .= '<a href="' . $_current_page . ($_page - 1) . $str_fragment . '" ' . $str_target_frame . ' class="page_link prev_link">' . $_prev . '</a>' . "\n";
        }

        // 6. HITUNG STRATEGI SLIDING WINDOW (2 Sebelum dan 2 Sesudah)
        $side_links = 2; 
        $_pager_offset = $_page - $side_links;
        $_pager_max    = $_page + $side_links;

        if ($_pager_offset < 1) {
            $_pager_max = $_pager_max + (1 - $_pager_offset);
            $_pager_offset = 1;
        }

        if ($_pager_max > $_num_page_total) {
            $_pager_offset = $_pager_offset - ($_pager_max - $_num_page_total);
            $_pager_max = $_num_page_total;
            if ($_pager_offset < 1) {
                $_pager_offset = 1;
            }
        }

        // 7. FITUR BARU: ELIPSIS AWAL (...)
        if ($_pager_offset > 1) {
            $_buffer .= '<a href="' . $_current_page . '1' . $str_fragment . '" ' . $str_target_frame . ' class="page_link">1</a>' . "\n";
            if ($_pager_offset > 2) {
                $_buffer .= '<span class="paging-ellipsis">...</span>' . "\n";
            }
        }

        // 8. LOOPING NOMOR HALAMAN DINAMIS
        for ($p = $_pager_offset; $p <= $_pager_max; $p++) {
            if ($p == $_page) {
                $_buffer .= '<span class="page_link current active">' . $p . '</span>' . "\n";
            } else {
                $_buffer .= '<a href="' . $_current_page . $p . $str_fragment . '" ' . $str_target_frame . ' class="page_link">' . $p . '</a>' . "\n";
            }
        }

        // 9. FITUR BARU: ELIPSIS AKHIR (...)
        if ($_pager_max < $_num_page_total) {
            if ($_pager_max < $_num_page_total - 1) {
                $_buffer .= '<span class="paging-ellipsis">...</span>' . "\n";
            }
            $_buffer .= '<a href="' . $_current_page . $_num_page_total . $str_fragment . '" ' . $str_target_frame . ' class="page_link">' . $_num_page_total . '</a>' . "\n";
        }

        // 10. LINK NEXT & LAST
        if ($_page < $_num_page_total) {
            $_buffer .= '<a href="' . $_current_page . ($_page + 1) . $str_fragment . '" ' . $str_target_frame . ' class="page_link next_link">' . $_next . '</a>' . "\n";
            $_buffer .= '<a href="' . $_current_page . $_num_page_total . $str_fragment . '" ' . $str_target_frame . ' class="page_link last_link">' . $_last . '</a>' . "\n";
        }

        $_buffer .= '</span></div>';

        return $_buffer;
    }

    /**
     * Helper privat untuk membersihkan data array query secara rekursif dari celah XSS
     */
    private static function cleanQueryArray(array $array)
    {
        $cleaned = [];
        foreach ($array as $key => $value) {
            $safe_key = class_exists('simbio_security') ? simbio_security::xssFree($key) : $key;
            if (is_array($value)) {
                $cleaned[$safe_key] = self::cleanQueryArray($value);
            } else {
                $cleaned[$safe_key] = class_exists('simbio_security') ? simbio_security::xssFree($value) : $value;
            }
        }
        return $cleaned;
    }
}
