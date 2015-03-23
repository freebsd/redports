<?php

require_once 'lib/Config.php';
require_once 'lib/Session.php';
require_once 'lib/functions.php';

require_once 'lib/Slim/Slim.php';

\Slim\Slim::registerAutoloader();

$db = new PDO(Config::get('datasource'));
$app = new \Slim\Slim();

/* Index */
$app->get('/', function() use ($app) {
   $app->response->headers->set('Content-Type', 'text/plain');
   $app->response->write('nothing to see here');
});

/* Jobs */
$app->get('/jobs/:jobid', 'isAllowed', function($jobid) use ($app) {
   $app->response->headers->set('Content-Type', 'application/json');
   $app->response->write(json_encode(array("jobid" => $jobid)));
})->conditions(array('jobid' => '[0-9]{1,}'));

$app->run();

