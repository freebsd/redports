<?php

require_once 'lib/Config.php';
require_once 'lib/Session.php';
require_once 'lib/functions.php';
require_once 'lib/GitHubWebhook.php';

require_once 'vendor/autoload.php';

$db = new PDO(Config::get('datasource'));
$app = new \Slim\Slim();

/* Index */
$app->get('/', function() use ($app) {
   $app->redirect('https://decke.github.io/redports/', 301);
});

/* GitHub Webhooks */
$app->post('/github/', function() use ($app) {
   $app->response->headers->set('Content-Type', 'text/plain');

   switch($app->request->headers->get('X-GitHub-Event'))
   {
      case 'ping':
         $app->response->write('pong');
      break;
      case 'push':
         if(handleGitHubPushEvent(json_decode($app->request->post('payload'))))
             $app->response->write('ok');
         else
             $app->halt(500, 'Webhook request failed');
      break;
      default:
         $app->response->write('Event type not implemented');
      break;
   }
});

/* Jobs */
$app->get('/api/jobs/:jobid', 'isAllowed', function($jobid) use ($app) {
   $app->response->headers->set('Content-Type', 'application/json');
   $app->response->write(json_encode(array("jobid" => $jobid)));
})->conditions(array('jobid' => '[0-9]{1,}'));

$app->run();

