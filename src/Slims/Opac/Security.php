<?php

namespace Slims\Opac;

class Security {
    public static function getCsrfToken() {
        return bin2hex(random_bytes(35));
    }

    public static function checkCsrfToken($session_token, $get_token) {
        if ( (isset($session_token)) AND (isset($get_token)) ) {
            $csrf_token = filter_input(INPUT_GET, 'csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if (!$csrf_token || $csrf_token !== $session_token) {
                return FALSE;
            } else {
                return TRUE;
            }
        }
    }

}


