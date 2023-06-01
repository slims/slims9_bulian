<?php

/**
 * @author              : Waris Agung Widodo
 * @Date                : 2017-07-04 15:28:21
 * @Last Modified by    : ido
 * @Last Modified time  : 2017-07-05 15:04:29
 *
 * Copyright (C) 2017  Waris Agung Widodo (ido.alit@gmail.com)
 */

$header = getallheaders();

if ((isset($header['SLiMS-Http-Cache']) || isset($header['slims-http-cache']))) {
    if ($sysconf['http']['cache']['lifetime'] > 0) header('Cache-Control: max-age=' . $sysconf['http']['cache']['lifetime']);
}

/*----------  Require dependencies  ----------*/
require 'lib/router.inc.php';
require __DIR__ . '/controllers/HomeController.php';
require __DIR__ . '/controllers/BiblioController.php';
require __DIR__ . '/controllers/MemberController.php';
require __DIR__ . '/controllers/SubjectController.php';
require __DIR__ . '/controllers/ItemController.php';
require __DIR__ . '/controllers/LoanController.php';

/*----------  Create router object  ----------*/
$router = new Router($sysconf, $dbs);
$router->setBasePath('api');

/*----------  Create routes  ----------*/
$router->map('GET', '/', 'HomeController@index');
$router->map('GET', '/biblio/popular', 'BiblioController@getPopular');
$router->map('GET', '/biblio/latest', 'BiblioController@getLatest');
$router->map('GET', '/subject/popular', 'SubjectController@getPopular');
$router->map('GET', '/subject/latest', 'SubjectController@getLatest');
$router->map('GET', '/member/top', 'MemberController@getTopMember');
$router->map('GET', '/biblio/gmd/[*:gmd]', 'BiblioController@getByGmd');
$router->map('GET', '/biblio/coll_type/[*:coll_type]', 'BiblioController@getByCollType');

/*----------  Admin  ----------*/
$router->map('GET', '/biblio/total/all', 'BiblioController@getTotalAll');
$router->map('GET', '/item/total/all', 'ItemController@getTotalAll');
$router->map('GET', '/item/total/lent', 'ItemController@getTotalLent');
$router->map('GET', '/item/total/available', 'ItemController@getTotalAvailable');
$router->map('GET', '/loan/summary', 'LoanController@getSummary');
$router->map('GET', '/loan/getdate/[*:start_date]', 'LoanController@getDate');
$router->map('GET', '/loan/summary/[*:date]', 'LoanController@getSummaryDate');

/*----------  Custom route based on hook plugin  ----------*/
\SLiMS\Plugins::getInstance()->execute('custom_api_route', ['router' => $router]);

/*----------  Run matching route  ----------*/
$router->run();

// doesn't need template
exit();