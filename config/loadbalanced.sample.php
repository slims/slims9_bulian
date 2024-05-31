<?php
/**
 * If SLiMS is setup behind reverse proxy
 * and it configure with load balance schema
 * you must enable it
 */
return [
    'env' => false,
    'options' => [
      'host' => 'host', // prevent host spoofing
      'source_ip' => 'HTTP_X_FORWARDED_FOR',
      'scheme' => 'http',
      'port' => 80,
    ]
];
