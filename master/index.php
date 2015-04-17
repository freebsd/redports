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

$session = new Session();

$app = new \Slim\Slim();
$app->config('debug', Config::get('debug'));
$app->response->headers->set('Content-Type', 'text/plain');

$redis = new Redis();
$redis->pconnect(Config::get('datasource'));

Resque::setBackend(Config::get('datasource'));

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

/* Queues - info about a queue */
$app->get('/queues/:queuename/:jailname/', 'isAllowed', function($queuename, $jailname) use ($app) {
   $queue = new Queue($queuename, $jailname);

   if($queue->exists())
      jsonResponse(200, $queue->getQueueInfo());
   else
      textResponse(404, 'Queue unknown');
});

/* Queues - Take next job */
$app->get('/queues/waitqueue/:jailname/take', 'isAllowed', function($jailname) use ($app) {
   $queue = new Queue('waitqueue', $jailname);
   $job = $queue->getNextJob();
   if($job === false)
      textResponse(204);

   $machine = new Machine(Session::getMachineId());
   $machine->addJob($job->getJobId());
   $job->set('machine', $machine->getName());
   $job->moveToQueue('runqueue');
   jsonResponse(200, $job->getJobData());
});

/* Jobs - Create new job */
$app->post('/jobs/create', 'isAllowed', function() use ($app) {
   $queue = new Queue();

   $jail = new Jails();
   if(!$jail->exists($_POST['jail']))
      textResponse(404, 'Jail unknown');

   $repos = new Repositories();
   if(!$repos->exists($_POST['repository']))
      textResponse(404, 'Repository unknown');

   $jobgroup = new Jobgroup($_POST['jobgroup']);
   if($jobgroup->exists())
      textResponse(403, 'Jobgroup already exists');

   $data = array(
      'port' => $_POST['port'],
      'jail' => $_POST['jail'],
      'repository' => $_POST['repository'],
      'jobgroup' => $_POST['jobgroup']
   );

   $job = $queue->createJob($data);
   $jobgroup->addJob($job->getJobId());

   jsonResponse(200, $job->getJobData());
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
      textResponse(404, 'Job not found');

   $machine = new Machine(Session::getMachineId());
   if(!$machine->hasJob($jobid))
      textResponse(403, 'Job not assigned to you');

   $filepath = Config::get('logdir').'/'.$jobid.'/'.basename($filename);

   if(file_exists($filepath))
      textResponse(403, 'File already exists');
   
   if(!is_dir(dirname($filepath)))
      mkdir(dirname($filepath), 0777, true);

   $fi = fopen('php://input', 'rb');
   $fo = fopen($filepath, 'w');

   stream_copy_to_stream($fi, $fo);

   fclose($fo);
   fclose($fi);

   $job->set('logfile', $filename);
   $job->save();

   textResponse(200);
})->conditions(array('jobid' => '[0-9]'));

/* Jobs - Finish a job (with buildstatus and buildreason) */
$app->post('/jobs/:jobid/finish', 'isAllowed', function($jobid) use ($app) {

   if(!isset($_POST['buildstatus']) || !isset($_POST['buildreason']))
      textResponse(400, 'Post data missing');

   $job = new Job($jobid);
   if($job === false)
      textResponse(204);

   $machine = new Machine(Session::getMachineId());
   if(!$machine->hasJob($job->getJobId()))
      textResponse(403, 'Job not assigned to you');

   $job->unset('machine');
   $job->set('buildstatus', $_POST['buildstatus']);
   $job->set('buildreason', $_POST['buildreason']);
   $job->moveToQueue('archivequeue');
   jsonResponse(200, $job->getJobData());
})->conditions(array('jobid' => '[0-9]'));

/* Jobgroup - List details of jobgroup */
$app->get('/group/:groupid/', 'isAllowed', function($groupid) use ($app) {

   $jobgroup = new Jobgroup($groupid);
   if(!$jobgroup->exists())
      textResponse(404, 'Jobgroup not found');

   jsonResponse(200, $jobgroup->getGroupInfo());
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

