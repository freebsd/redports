<?php

require_once 'vendor/autoload.php';
require_once 'lib/autoload.php';
require_once 'lib/functions.php';

$app = new \Slim\Slim();
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

/* Jobs */
$app->get('/api/jobs/:jobid', 'isAllowed', function($jobid) use ($app) {
   $app->response->headers->set('Content-Type', 'application/json');
   $app->response->write(json_encode(array("jobid" => $jobid)));
})->conditions(array('jobid' => '[0-9]{1,}'));

$app->run();

