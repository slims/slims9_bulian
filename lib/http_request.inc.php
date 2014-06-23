<?php
/**
 * 2010 Arie Nugraha (dicarve@yahoo.com)
 *
 * This class is taken and modified from PHP script in:
 * http://www.fijiwebdesign.com/fiji-web-design-blog/acess-the-http-request-headers-and-body-via-php.html
 * by Fiji Web Design
 *
 * I don't know license of this class, but if someone know or disagree, please
 * send me an e-mail to me
 *
 * The send_http_request method is taken and modified from Jonas John's PHP script
 * found on : http://www.jonasjohn.de/snippets. Public Domain licensed
 *
 */

// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

/**
* Access the HTTP Request
*/
class http_request {

    /** additional HTTP headers not prefixed with HTTP_ in $_SERVER superglobal */
    private $add_headers = array('CONTENT_TYPE', 'CONTENT_LENGTH');
    private $protocol = false;
    private $body = null;
    private $error = false;
    private $method = 'GET';
    private $request_method = false;
    private $headers = array();


    /**
    * Construtor
    * Retrieve HTTP Body
    * @param Array Additional Headers to retrieve
    */
    public function get_http_request($add_headers = false) {
        $this->retrieve_headers($add_headers);
        $this->body = @file_get_contents('php://input');
    }


    /**
    * Retrieve the HTTP request headers from the $_SERVER superglobal
    * @param Array Additional Headers to retrieve
    */
    private function retrieve_headers($add_headers = false) {
        if ($add_headers) {
            $this->add_headers = array_merge($this->add_headers, $add_headers);
        }

        if (isset($_SERVER['HTTP_METHOD'])) {
            $this->method = $_SERVER['HTTP_METHOD'];
            unset($_SERVER['HTTP_METHOD']);
        } else {
            $this->method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;
        }
        $this->protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : false;
        $this->request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : false;

        $this->headers = array();
        foreach($_SERVER as $i=>$val) {
            if (strpos($i, 'HTTP_') === 0 || in_array($i, $this->add_headers)) {
                $name = str_replace(array('HTTP_', '_'), array('', '-'), $i);
                $this->headers[$name] = $val;
            }
        }
    }


    /**
    * Retrieve HTTP Method
    */
    public function method() {
        return $this->method;
    }


    /**
    * Retrieve HTTP Body
    */
    public function body() {
        return $this->body;
    }


    /**
    * Retrieve HTTP request error
    * @return   array
    */
    public function error() {
        return $this->error;
    }


    /**
    * Retrieve all HTTP Headers
    * @param    string  $name: optionael header name to retrieve
    * @return   mixed
    */
    public function headers($name = '') {
        if ($name) {
            $name = strtoupper($name);
            return isset($this->headers[$name]) ? $this->headers[$name] : false;
        }
	    return $this->headers;
    }


    /**
     * Send HTTP request
     * @param   string  $url: URL where request is sent
     * @param   string  $referer: HTTP referer
     * @param   mixed   $data: string or an array of data to send
     * @param   string  $method: HTTP request method
     * @param   string  $content_type: content type of request
     */
    public function send_http_request($url, $referer, $data, $method = 'POST', $content_type = 'application/x-www-form-urlencoded') {
        if ($content_type == 'text/json') {
            if (is_string($data)) {
                // raw data
                $encoded_data = $data;
            } else {
                // convert array to JSON format
                $encoded_data = json_encode($data);
            }
        } else if ($content_type != 'text/json' && $content_type != 'application/x-www-form-urlencoded') {
            // raw data
            $encoded_data = $data;
        } else {
            if (is_string($data)) {
                // raw data
                $encoded_data = $data;
            } else {
                // convert variables array to URL encoded
                $encoded_data = http_build_query($data);
            }
        }

        // parse the given URL
        $url = parse_url($url);
        if ($url['scheme'] != 'http') {
            return false;
        }

        // extract host, port and path:
        $host = $url['host'];
        $port = isset($url['port']) ? $url['port'] : '80'; // using port 80 for undefined port number
        $path = $url['path'];

        // open a socket connection
        $fp = fsockopen($host, $port, $errno, $errstr, 30);
        if (!$fp) {
            $this->error = array('errno' => $errno, 'message' => $errstr);
            return false;
        }

        // send the request headers:
        $method = strtoupper($method);
        fputs($fp, "$method $path HTTP/1.1\r\n");
        fputs($fp, "Host: $host" . (($port != '80') ? ":$port" : '') . "\r\n");
        fputs($fp, "Referer: $referer\r\n");
        fputs($fp, "Content-type: $content_type\r\n");
        fputs($fp, "Content-length: ". strlen($encoded_data) ."\r\n");
        fputs($fp, "Accept: */*\r\n");
        fputs($fp, "Connection: close\r\n\r\n");
        fputs($fp, $encoded_data);

        $result = '';
        while(!feof($fp)) {
            // receive the results of the request
            $result .= fgets($fp, 1024);
        }

        // close the socket connection:
        fclose($fp);

        // split the result header from the content
        $result = explode("\r\n\r\n", $result, 2);

        $this->headers = isset($result[0]) ? $result[0] : '';
        $this->body = isset($result[1]) ? $result[1] : '';
    }
}
