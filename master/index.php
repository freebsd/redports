<?php

/**
 * redports is a continuous integration platform for FreeBSD ports
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
 */

require_once 'vendor/autoload.php';
require_once 'lib/autoload.php';
require_once 'lib/functions.php';

$session = new Session();

$app = new \Slim\Slim();
$app->config('debug', Config::get('debug'));
$app->response->headers->set('Content-Type', 'text/plain');

$redis = new Redis();
$redis->pconnect(Config::get('datasource'));

/* GitHub Webhooks */
$app->post('/github/', function() use ($app) {
   $github = new GitHubWebhook();
   $result = $github->handleEvent($app->request->headers->get('X-GitHub-Event'),
      $app->request->post('payload'));

   textResponse($result['code'], $result['message']);
});

/* Authentication - Login */
$app->post('/auth/', function() use ($app, $session) {
   if(!isset($_POST['machineid']) || !isset($_POST['secret']))
      textResponse(403, 'Authentication failed');
   else if(!$session->login($_POST['machineid'], $_POST['secret']))
      textResponse(403, 'Authentication failed');
   else
      jsonResponse(200, array('sessionid' => $session->getSessionId()));
});

$app->get('/auth/', function() use ($app) {
   textResponse(400, 'Only POST method allowed for authentication');
});

/* Jails - List all jails */
$app->get('/jails/', 'isAllowed', function() use ($app) {
   $jails = new Jails();
   jsonResponse(200, $jails->getJails());
});

/* Jails - List individual jail info */
$app->get('/jails/:jailname/', 'isAllowed', function($jailname) use ($app) {
   $jails = new Jails();

   if(!$jails->exists($jailname))
      textResponse(404, 'Jail unknown');
   else
      jsonResponse(200, $jails->getJail($jailname));
});

/* Queues - statistics for a queue */
$app->get('/queues/:queuename/:jailname/', 'isAllowed', function($queuename, $jailname) use ($app) {
   $queue = new Queue($queuename, $jailname);

   if($queue->exists())
   {
      $data = array(
         'numjobs' => $queue->countJobs(),
         'jail' => $jailname,
         'queue' => $queuename
      );

      jsonResponse(200, $data);
   }
   else
      textResponse(404, 'Queue unknown');
});

/* Queues - Take next job */
$app->get('/queues/waitqueue/:jailname/take', 'isAllowed', function($jailname) use ($app) {
   $queue = new Queue('waitqueue', $jailname);
   $job = $queue->getNextJob();
   if($job === false)
      textResponse(204);
   else
   {
      $job->moveToQueue('runqueue');
      jsonResponse(200, $job->getJobData());
   }
});

/* Jobs - Create new job */
$app->get('/jobs/create', 'isAllowed', function() use ($app) {
   textResponse(501, 'Not implemented');
});

/* Jobs - Job details */
$app->get('/jobs/:jobid/', 'isAllowed', function($jobid) use ($app) {
   $job = new Job($jobid);
   if(!$job->exists())
      textResponse(404, 'Job not found');
   else
      jsonResponse(200, $job->getJobData());
})->conditions(array('jobid' => '[0-9]'));

/* Jobs - Upload logfile ... */
$app->put('/jobs/:jobid/logfile/:filename', 'isAllowed', function($jobid, $filename) use ($app) {
   $job = new Job($jobid);
   if(!$job->exists())
      textResponse(404, "Job not found");

   $filepath = Config::get('logdir').'/'.$jobid.'/'.basename($filename);

   if(file_exists($filepath))
      textResponse(403, "File already exists");
   
   if(!is_dir(dirname($filepath)))
      mkdir(dirname($filepath), 0777, true);

   $fi = fopen("php://input", "rb");
   $fo = fopen($filepath, "w");

   stream_copy_to_stream($fi, $fo);

   fclose($fo);
   fclose($fi);

   $job->set('logfile', $filename);
   $job->save();

   textResponse(200);
})->conditions(array('jobid' => '[0-9]'));

/* Jobs - Finish a job (with resultcode and buildstatus) */
$app->post('/jobs/:jobid/finish', 'isAllowed', function($jobid) use ($app) {
   textResponse(501, 'Not implemented');
})->conditions(array('jobid' => '[0-9]'));

/* Jobgroup - List details of jobgroup */
$app->get('/group/:groupid/', 'isAllowed', function($groupid) use ($app) {
   textResponse(501, 'Not implemented');
});


/* 404 - not found */
$app->notFound(function() use ($app) {
   textResponse(404, 'Not found');
});

/* 500 - internal server error */
$app->error(function(\Exception $e) use ($app) {
   textResponse(500, 'Internal Server Error');
});

$app->run();

