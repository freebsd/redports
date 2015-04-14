<?php

require_once 'vendor/autoload.php';
require_once 'lib/autoload.php';
require_once 'lib/functions.php';

$app = new \Slim\Slim();
$app->response->headers->set('Content-Type', 'application/json');

$redis = new Redis();
$redis->pconnect(Config::get('datasource'));

/* Index */
$app->get('/', function() use ($app) {
   $app->redirect('https://decke.github.io/redports/', 301);
});

/* GitHub Webhooks */
$app->post('/github/', function() use ($app) {
   $app->response->headers->set('Content-Type', 'text/plain');

   $github = new GitHubWebhook();
   $result = $github->handleEvent($app->request->headers->get('X-GitHub-Event'),
      $app->request->post('payload'));

   if($result['code'] == 200)
      $app->response->write($result['message']);
   else
      $app->halt($result['code'], $result['message']);
});

/* Authentication - Login */
$app->post('/auth/', function() use ($app) {
   if(Session::login($_POST['id'], $_POST['secret']))
      $app->response->write(json_encode(array("status" => "okay")));
   else
      $app->response->write(json_encode(array("status" => "failed")));
});

/* Jails - List all jails */
$app->get('/jails/', 'isAllowed', function() use ($app) {
   $jails = new Jails();
   $app->response->write(json_encode($jails->getJails()));
});

/* Jails - List individual jail info */
$app->get('/jails/:jailname/', 'isAllowed', function($jailname) use ($app) {
   $jails = new Jails();

   if(!$jails->exists($jailname))
      $app->response->write(json_encode(array('status' => 'Jail unknown')));
   else
      $app->response->write(json_encode($jails->getJail($jailname)));
});

/* Queues - statistics for a queue */
$app->get('/queues/:queuename/:jailname/', 'isAllowed', function($queuename, $jailname) use ($app) {
   $app->halt(501, 'Not implemented');
});

/* Queues - Take next job */
$app->get('/queues/:queuename/:jailname/take', 'isAllowed', function($queuename, $jailname) use ($app) {
   $app->halt(501, 'Not implemented');
});

/* Jobs - Create new job */
$app->get('/jobs/create', 'isAllowed', function() use ($app) {
   $app->halt(501, 'Not implemented');
});

/* Jobs - Job details */
$app->get('/jobs/:jobid/', 'isAllowed', function($jobid) use ($app) {
   $app->halt(501, 'Not implemented');
})->conditions(array('jobid' => '[0-9]'));

/* Jobs - Upload logfile/portstree ... */
$app->post('/jobs/:jobid/upload', 'isAllowed', function($jobid) use ($app) {
   $app->halt(501, 'Not implemented');
})->conditions(array('jobid' => '[0-9]'));

/* Jobs - Finish a job (with resultcode and buildstatus) */
$app->post('/jobs/:jobid/finish', 'isAllowed', function($jobid) use ($app) {
   $app->halt(501, 'Not implemented');
})->conditions(array('jobid' => '[0-9]'));

/* Jobgroup - List details of jobgroup */
$app->get('/group/:groupid/', 'isAllowed', function($groupid) use ($app) {
   $app->halt(501, 'Not implemented');
});

$app->run();

