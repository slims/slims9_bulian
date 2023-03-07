<?php
/**
 * utility class
 * A Collection of static utility methods
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
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

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

class utility
{
    /**
     * Static Method to send out javascript alert
     *
     * @param   string  $str_message
     * @return  void
     */
    public static function jsAlert($str_message)
    {
        if (!$str_message) {
            return;
        }

        // replace newline with javascripts newline
        $str_message = str_replace("\n", '\n', addslashes($str_message));
        echo '<script type="text/javascript">'."\n";
        echo 'alert("'.$str_message.'")'."\n";
        echo '</script>'."\n";
    }

  /**
   * Static Method to send out toastr notification
   *
   * @param   string $type [info, success, warning, error]
   * @param   string $str_message
   * @return  void
   */
    public static function jsToastr($title, $str_message, $type = 'info')
    {
      if (!$str_message) {
        return;
      }

      $options = [
        'closeButton' => true,
        'debug' => false,
        'newestOnTop' => false,
        'progressBar' => false,
        'positionClass' => 'toast-top-right',
        'preventDuplicates' => false,
        'onclick' => null,
        'showDuration' => 300,
        'hideDuration' => 1000,
        'timeOut' => 5000,
        'extendedTimeOut' => 1000,
        'showEasing' => 'swing',
        'hideEasing' => 'linear',
        'showMethod' => 'fadeIn',
        'hideMethod' => 'fadeOut'
      ];

      // replace newline with javascripts newline
      $str_message = str_replace("\n", '\n', addslashes($str_message));
      echo '<script type="text/javascript">'."\n";
      echo 'top.toastr.'.$type.'("'.$str_message.'", "'.$title.'", '.json_encode($options).')'."\n";
      echo '</script>'."\n";
    }


    /**
     * Static Method to create random string
     *
     * @param   int     $int_num_string: number of randowm string to created
     * @return  void
     */
    public static function createRandomString($int_num_string = 32)
    {
      $_random = '';
      $_salt = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $_saltlength = strlen($_salt);
      for ($r = 0; $r < $int_num_string; $r++) {
        $_random .= $_salt[rand(0, $_saltlength - 1)];
      }

      return $_random;
    }


    /**
     * Static Method to load application settings from database
     *
     * @param   object  $obj_db
     * @return  void
     */
    public static function loadSettings($obj_db)
    {
        global $sysconf;
        $_setting_query = $obj_db->query('SELECT * FROM setting');
        if (!$obj_db->errno) {
            while ($_setting_data = $_setting_query->fetch_assoc()) {
                $_value = @unserialize($_setting_data['setting_value']);
                if (is_array($_value)) {
                    // make sure setting is available before
                    if (!isset($sysconf[$_setting_data['setting_name']]))
                        $sysconf[$_setting_data['setting_name']] = [];

                    foreach ($_value as $_idx => $_curr_value) {
                        // convert default setting value into array
                        if (!is_array($sysconf[$_setting_data['setting_name']]))
                            $sysconf[$_setting_data['setting_name']] = [$sysconf[$_setting_data['setting_name']]];
                        $sysconf[$_setting_data['setting_name']][$_idx] = $_curr_value;
                    }
                } else {
                    $sysconf[$_setting_data['setting_name']] = stripcslashes($_value??'');
                }
            }
        }
    }


    /**
     * Static Method to check privileges of application module form current user
     *
     * @param   string  $str_module_name
     * @param   string  $str_privilege_type
     * @return  boolean
     */
    public static function havePrivilege($str_module_name, $str_privilege_type = 'r')
    {
        global $sysconf;
        // checking checksum
        if ($sysconf['load_balanced_env']) {
            $server_addr = ip();
        } else {
            $server_addr = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : gethostbyname($_SERVER['SERVER_NAME']));
        }


        $_checksum = defined('UCS_BASE_DIR')?md5($server_addr.UCS_BASE_DIR.'admin'):md5($server_addr.SB.'admin');
        if (!isset($_SESSION['checksum']) || $_SESSION['checksum'] != $_checksum) {
            return false;
        }
        // check privilege type
        if (!in_array($str_privilege_type, array('r', 'w'))) {
            return false;
        }
        if (isset($_SESSION['priv'][$str_module_name][$str_privilege_type]) AND $_SESSION['priv'][$str_module_name][$str_privilege_type]) {
            return true;
        }
        return false;
    }


    /**
     * Static Method to write application activities logs
     *
     * @param   object  $obj_db
     * @param   string  $str_log_type
     * @param   string  $str_value_id
     * @param   string  $str_location
     * @param   string  $str_log_msg
     * @return  void
     */
    public static function writeLogs($obj_db, $str_log_type, $str_value_id, $str_location, $str_log_msg, $str_log_submod='', $str_log_action='')
    {
        if (!$obj_db->error) {
            // log table
            $_log_date = date('Y-m-d H:i:s');
            $_log_table = 'system_log';
            // filter input
            $str_log_type = $obj_db->escape_string(trim($str_log_type));
            $str_value_id = $obj_db->escape_string(trim($str_value_id));
            $str_location = $obj_db->escape_string(trim($str_location));
            $str_log_msg = $obj_db->escape_string(trim($str_log_msg));
            $str_log_submod = $obj_db->escape_string(trim($str_log_submod));
            $str_log_action = $obj_db->escape_string(trim($str_log_action));
            // insert log data to database
            @$obj_db->query('INSERT INTO '.$_log_table.'
            VALUES (NULL, \''.$str_log_type.'\', \''.$str_value_id.'\', \''.$str_location.'\','.
             ' \''.$str_log_submod.'\''.
             ', \''.$str_log_action.'\''.
             ', \''.$str_log_msg.'\', \''.$_log_date.'\')');
        }
    }


    /**
     * Static Method to get an ID of database table record
     *
     * @param   object  $obj_db
     * @param   string  $str_table_name
     * @param   string  $str_id_field
     * @param   string  $str_value_field
     * @param   string  $str_value
     * @param   array   $arr_cache
     * @return  mixed
     */
    public static function getID($obj_db, $str_table_name, $str_id_field, $str_value_field, $str_value, &$arr_cache = false)
    {
        $str_value = trim($str_value);
        if ($arr_cache) {
            if (isset($arr_cache[$str_value])) {
                return $arr_cache[$str_value];
            }
        }
        if (!$obj_db->error) {
            $id_q = $obj_db->query('SELECT '.$str_id_field.' FROM '.$str_table_name.' WHERE '.$str_value_field.'=\''.$obj_db->escape_string($str_value).'\'');
            if ($id_q->num_rows > 0) {
                $id_d = $id_q->fetch_row();
                unset($id_q);
                // cache
                if ($arr_cache) {
                    $arr_cache[$str_value] = $id_d[0];
                }
                return $id_d[0];
            } else {
                $_curr_date = date('Y-m-d');
                // if not found then we insert it as new value
                $obj_db->query('INSERT IGNORE INTO '.$str_table_name.' ('.$str_value_field.', input_date, last_update)
                    VALUES (\''.$obj_db->escape_string($str_value).'\', \''.$_curr_date.'\', \''.$_curr_date.'\')');
                if (!$obj_db->error) {
                    // cache
                    if ($arr_cache) {
                        $arr_cache[$str_value] = $obj_db->insert_id;
                    }
                    return $obj_db->insert_id;
                }
            }
        }
    }


    /**
     * Static method to detect mobile browser
     *
     * @return  boolean
     * this script is taken from http://detectmobilebrowsers.com/
     **/
    public static function isMobileBrowser()
    {
        $_is_mobile_browser = false;

        if(preg_match('/android.+mobile|avantgo|bada\/|blackberry|
            blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|
            iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|
            palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|
            treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|
            xda|xiino/i',
        @$_SERVER['HTTP_USER_AGENT'] ?? '')
        || preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|
            a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|
            amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|
            au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|
            br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|
            cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|
            do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|
            ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|
            go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|
            hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|
            i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|
            ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |
            kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|
            libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|
            me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|
            t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|
            n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|
            nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|
            pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|
            psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|
            raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|
            sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|
            sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|
            so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|
            ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|
            ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|
            vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|
            vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|
            wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i',
        substr(@$_SERVER['HTTP_USER_AGENT'] ?? '',0,4)))
            $_is_mobile_browser = true;

        return $_is_mobile_browser;
    }


    /**
     * Static method to check if member already logged in or not
     *
     * @return  boolean
     **/
    public static function isMemberLogin()
    {
        $_logged_in = false;
        $_logged_in = isset($_SESSION['mid']) && isset($_SESSION['m_name']);
        return $_logged_in;
    }


    /**
     * Static method to filter data
     *
     * @param   mixed   $mix_input: input data
     * @param   string  $str_input_type: input type
     * @param   boolean $bool_trim: are input string trimmed
     *
     * @return  mixed
     **/
    public static function filterData($mix_input, $str_input_type = 'get', $bool_escape_sql = true, $bool_trim = true, $bool_strip_html = false) {
        global $dbs;

        if (extension_loaded('filter')) {
            if ($str_input_type == 'var') {
                $mix_input = filter_var($mix_input, FILTER_SANITIZE_STRING);
            } else if ($str_input_type == 'post') {
                $mix_input = filter_input(INPUT_POST, $mix_input);
            } else if ($str_input_type == 'cookie') {
                $mix_input = filter_input(INPUT_COOKIE, $mix_input);
            } else if ($str_input_type == 'session') {
                $mix_input = filter_input(INPUT_SESSION, $mix_input);
            } else if ($str_input_type == 'server') {
                $mix_input = $_SERVER[$mix_input]??null;
            } else {
                $mix_input = filter_input(INPUT_GET, $mix_input);
            }
        } else {
            if ($str_input_type == 'get') {
                $mix_input = $_GET[$mix_input]??null;
            } else if ($str_input_type == 'post') {
                $mix_input = $_POST[$mix_input]??null;
            } else if ($str_input_type == 'cookie') {
                $mix_input = $_COOKIE[$mix_input]??null;
            } else if ($str_input_type == 'session') {
                $mix_input = $_SESSION[$mix_input]??null;
            } else if ($str_input_type == 'server') {
                $mix_input = $_SERVER[$mix_input]??null;
            } 
        }

        if (!is_null($mix_input)) {
            // trim whitespace on string
            if ($bool_trim) { $mix_input = trim($mix_input); }
            // strip html
            if ($bool_strip_html) { $mix_input = strip_tags($mix_input); }
            // escape SQL string
            if ($bool_escape_sql) { $mix_input = $dbs->escape_string($mix_input); }
        }

        return $mix_input;
    }


    /**
     * Static method to convert XML entities
     * Code taken and modified from:
     * Matt Robinson at http://inanimatt.com/php-convert-entities.html
     *
     * @param   string  $str_xml_data: string of xml data to check
     *
     * @return  string
     **/
    public static function convertXMLentities($str_xml_data) {
      static $_ent_table = array('quot' => '&#34;',
        'amp' => '&#38;',
        'lt' => '&#60;',
        'gt' => '&#62;',
        'OElig' => '&#338;',
        'oelig' => '&#339;',
        'Scaron' => '&#352;',
        'scaron' => '&#353;',
        'Yuml' => '&#376;',
        'circ' => '&#710;',
        'tilde' => '&#732;',
        'ensp' => '&#8194;',
        'emsp' => '&#8195;',
        'thinsp' => '&#8201;',
        'zwnj' => '&#8204;',
        'zwj' => '&#8205;',
        'lrm' => '&#8206;',
        'rlm' => '&#8207;',
        'ndash' => '&#8211;',
        'mdash' => '&#8212;',
        'lsquo' => '&#8216;',
        'rsquo' => '&#8217;',
        'sbquo' => '&#8218;',
        'ldquo' => '&#8220;',
        'rdquo' => '&#8221;',
        'bdquo' => '&#8222;',
        'dagger' => '&#8224;',
        'Dagger' => '&#8225;',
        'permil' => '&#8240;',
        'lsaquo' => '&#8249;',
        'rsaquo' => '&#8250;',
        'euro' => '&#8364;',
        'fnof' => '&#402;',
        'Alpha' => '&#913;',
        'Beta' => '&#914;',
        'Gamma' => '&#915;',
        'Delta' => '&#916;',
        'Epsilon' => '&#917;',
        'Zeta' => '&#918;',
        'Eta' => '&#919;',
        'Theta' => '&#920;',
        'Iota' => '&#921;',
        'Kappa' => '&#922;',
        'Lambda' => '&#923;',
        'Mu' => '&#924;',
        'Nu' => '&#925;',
        'Xi' => '&#926;',
        'Omicron' => '&#927;',
        'Pi' => '&#928;',
        'Rho' => '&#929;',
        'Sigma' => '&#931;',
        'Tau' => '&#932;',
        'Upsilon' => '&#933;',
        'Phi' => '&#934;',
        'Chi' => '&#935;',
        'Psi' => '&#936;',
        'Omega' => '&#937;',
        'alpha' => '&#945;',
        'beta' => '&#946;',
        'gamma' => '&#947;',
        'delta' => '&#948;',
        'epsilon' => '&#949;',
        'zeta' => '&#950;',
        'eta' => '&#951;',
        'theta' => '&#952;',
        'iota' => '&#953;',
        'kappa' => '&#954;',
        'lambda' => '&#955;',
        'mu' => '&#956;',
        'nu' => '&#957;',
        'xi' => '&#958;',
        'omicron' => '&#959;',
        'pi' => '&#960;',
        'rho' => '&#961;',
        'sigmaf' => '&#962;',
        'sigma' => '&#963;',
        'tau' => '&#964;',
        'upsilon' => '&#965;',
        'phi' => '&#966;',
        'chi' => '&#967;',
        'psi' => '&#968;',
        'omega' => '&#969;',
        'thetasym' => '&#977;',
        'upsih' => '&#978;',
        'piv' => '&#982;',
        'bull' => '&#8226;',
        'hellip' => '&#8230;',
        'prime' => '&#8242;',
        'Prime' => '&#8243;',
        'oline' => '&#8254;',
        'frasl' => '&#8260;',
        'weierp' => '&#8472;',
        'image' => '&#8465;',
        'real' => '&#8476;',
        'trade' => '&#8482;',
        'alefsym' => '&#8501;',
        'larr' => '&#8592;',
        'uarr' => '&#8593;',
        'rarr' => '&#8594;',
        'darr' => '&#8595;',
        'harr' => '&#8596;',
        'crarr' => '&#8629;',
        'lArr' => '&#8656;',
        'uArr' => '&#8657;',
        'rArr' => '&#8658;',
        'dArr' => '&#8659;',
        'hArr' => '&#8660;',
        'forall' => '&#8704;',
        'part' => '&#8706;',
        'exist' => '&#8707;',
        'empty' => '&#8709;',
        'nabla' => '&#8711;',
        'isin' => '&#8712;',
        'notin' => '&#8713;',
        'ni' => '&#8715;',
        'prod' => '&#8719;',
        'sum' => '&#8721;',
        'minus' => '&#8722;',
        'lowast' => '&#8727;',
        'radic' => '&#8730;',
        'prop' => '&#8733;',
        'infin' => '&#8734;',
        'ang' => '&#8736;',
        'and' => '&#8743;',
        'or' => '&#8744;',
        'cap' => '&#8745;',
        'cup' => '&#8746;',
        'int' => '&#8747;',
        'there4' => '&#8756;',
        'sim' => '&#8764;',
        'cong' => '&#8773;',
        'asymp' => '&#8776;',
        'ne' => '&#8800;',
        'equiv' => '&#8801;',
        'le' => '&#8804;',
        'ge' => '&#8805;',
        'sub' => '&#8834;',
        'sup' => '&#8835;',
        'nsub' => '&#8836;',
        'sube' => '&#8838;',
        'supe' => '&#8839;',
        'oplus' => '&#8853;',
        'otimes' => '&#8855;',
        'perp' => '&#8869;',
        'sdot' => '&#8901;',
        'lceil' => '&#8968;',
        'rceil' => '&#8969;',
        'lfloor' => '&#8970;',
        'rfloor' => '&#8971;',
        'lang' => '&#9001;',
        'rang' => '&#9002;',
        'loz' => '&#9674;',
        'spades' => '&#9824;',
        'clubs' => '&#9827;',
        'hearts' => '&#9829;',
        'diams' => '&#9830;',
        'nbsp' => '&#160;',
        'iexcl' => '&#161;',
        'cent' => '&#162;',
        'pound' => '&#163;',
        'curren' => '&#164;',
        'yen' => '&#165;',
        'brvbar' => '&#166;',
        'sect' => '&#167;',
        'uml' => '&#168;',
        'copy' => '&#169;',
        'ordf' => '&#170;',
        'laquo' => '&#171;',
        'not' => '&#172;',
        'shy' => '&#173;',
        'reg' => '&#174;',
        'macr' => '&#175;',
        'deg' => '&#176;',
        'plusmn' => '&#177;',
        'sup2' => '&#178;',
        'sup3' => '&#179;',
        'acute' => '&#180;',
        'micro' => '&#181;',
        'para' => '&#182;',
        'middot' => '&#183;',
        'cedil' => '&#184;',
        'sup1' => '&#185;',
        'ordm' => '&#186;',
        'raquo' => '&#187;',
        'frac14' => '&#188;',
        'frac12' => '&#189;',
        'frac34' => '&#190;',
        'iquest' => '&#191;',
        'Agrave' => '&#192;',
        'Aacute' => '&#193;',
        'Acirc' => '&#194;',
        'Atilde' => '&#195;',
        'Auml' => '&#196;',
        'Aring' => '&#197;',
        'AElig' => '&#198;',
        'Ccedil' => '&#199;',
        'Egrave' => '&#200;',
        'Eacute' => '&#201;',
        'Ecirc' => '&#202;',
        'Euml' => '&#203;',
        'Igrave' => '&#204;',
        'Iacute' => '&#205;',
        'Icirc' => '&#206;',
        'Iuml' => '&#207;',
        'ETH' => '&#208;',
        'Ntilde' => '&#209;',
        'Ograve' => '&#210;',
        'Oacute' => '&#211;',
        'Ocirc' => '&#212;',
        'Otilde' => '&#213;',
        'Ouml' => '&#214;',
        'times' => '&#215;',
        'Oslash' => '&#216;',
        'Ugrave' => '&#217;',
        'Uacute' => '&#218;',
        'Ucirc' => '&#219;',
        'Uuml' => '&#220;',
        'Yacute' => '&#221;',
        'THORN' => '&#222;',
        'szlig' => '&#223;',
        'agrave' => '&#224;',
        'aacute' => '&#225;',
        'acirc' => '&#226;',
        'atilde' => '&#227;',
        'auml' => '&#228;',
        'aring' => '&#229;',
        'aelig' => '&#230;',
        'ccedil' => '&#231;',
        'egrave' => '&#232;',
        'eacute' => '&#233;',
        'ecirc' => '&#234;',
        'euml' => '&#235;',
        'igrave' => '&#236;',
        'iacute' => '&#237;',
        'icirc' => '&#238;',
        'iuml' => '&#239;',
        'eth' => '&#240;',
        'ntilde' => '&#241;',
        'ograve' => '&#242;',
        'oacute' => '&#243;',
        'ocirc' => '&#244;',
        'otilde' => '&#245;',
        'ouml' => '&#246;',
        'divide' => '&#247;',
        'oslash' => '&#248;',
        'ugrave' => '&#249;',
        'uacute' => '&#250;',
        'ucirc' => '&#251;',
        'uuml' => '&#252;',
        'yacute' => '&#253;',
        'thorn' => '&#254;',
        'yuml' => '&#255;'
        );

      // Entity not found? Destroy it.
      return isset($_ent_table[$str_xml_data[1]]) ? $_ent_table[$str_xml_data[1]] : '';
    }

    /**
     * Static Method to load admin template
     *
     * @param   object  $obj_db
     * @return  void
     */
    public static function loadUserTemplate($obj_db,$uid)
    {
      global $sysconf;
      // load user template settings for override setting
      $_q = $obj_db->query("SELECT admin_template FROM user WHERE user_id=$uid AND (admin_template!=NULL OR admin_template !='')");
      if($_q->num_rows>0){
        $template_settings = unserialize($_q->fetch_row()[0]);
        foreach ($template_settings as $setting_name => $setting_value) {
          $sysconf['admin_template'][$setting_name] = $setting_value;
        }
      }
    }

    public static function dlCount($obj_db, $str_file_id, $str_member_id, $str_user_id)
    {
        if (!$obj_db->error) {
            // log table
            $_log_date = date('Y-m-d H:i:s');
            $_log_table = 'files_read';
            // filter input
            $str_log_type = $obj_db->escape_string(trim($str_file_id));
            $str_value_id = $obj_db->escape_string(trim($str_member_id));
            $str_user_id = $obj_db->escape_string(trim($str_user_id));
            // insert log data to database
            @$obj_db->query('INSERT INTO `'.$_log_table.'` (`filelog_id`,`file_id`,`date_read`,`member_id`,`user_id`,`client_ip`) 
            VALUES (NULL, \''.$str_file_id.'\', \''.$_log_date.'\', \''.$str_value_id.'\', \''.$str_user_id.'\', \''.ip().'\')');
        }
    }
}
