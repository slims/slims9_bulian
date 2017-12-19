<?php

/**
 * @author              : Waris Agung Widodo
 * @Date                : 2017-07-04 15:28:21
 * @Last Modified by    : ido
 * @Last Modified time  : 2017-07-05 15:04:29
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

/*----------  Require dependencies  ----------*/
require 'lib/router.inc.php';
require __DIR__ . '/controllers/HomeController.php';
require __DIR__ . '/controllers/BiblioController.php';

/*----------  Create router object  ----------*/
$router = new Router($sysconf, $dbs);
$router->setBasePath('api');

/*----------  Create routes  ----------*/
$router->map('GET', '/', 'HomeController:index');
$router->map('GET', '/biblio/[i:id]/[a:token]', 'BiblioController:detail');

/*----------  Run matching route  ----------*/
$router->run();

// doesn't need template
exit();