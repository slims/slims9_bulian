<?php

return [
    /**
     * Configure base path of SLiMS.
     * 
     * If you setup your SLiMS behind reverse proxy
     * like nginx and take it with subfolder strategy
     * (e.g foo.com/slims/) this value must be fill up
     * with your path config in reverse proxy server
     */
    'base' => '',

    /**
     * Force Http schema
     * 
     * By default SLiMS url generator by default 
     * generate Http schema with only http:// if SLiMS
     * running on standart http port or SLiMS behind
     * proxy server. Set it to 'enable'.
     */
    'force_https' => false
];