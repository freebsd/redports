<?php

namespace Redports\Master;

/**
 * Handler for GitHub Webhooks.
 *
 * Whenever GitHub informs us about a new push/commit we
 * need to extract the information and create some new
 * jobs for all affected ports.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 *
 * @link       https://freebsd.github.io/redports/
 */
class GitHubWebhook
{
    public function __construct()
    {
    }

    public function handleEvent($event)
    {
        switch ($event) {
         case 'ping':
            return array('code' => 200, 'message' => 'pong');
         break;
         case 'push':
             $payload = json_decode(file_get_contents('php://input'), true);
             if ($this->push($payload)) {
                 return array('code' => 200, 'message' => 'ok');
             } else {
                 return array('code' => 500, 'message' => 'Webhook request failed');
             }
         break;
         default:
            return array('code' => 500, 'message' => 'Event type not implemented');
         break;
      }
    }

    public function push($payload)
    {
        $config = $this->_getUserConfig($payload['repository']['full_name'].'/'.$payload['commits'][0]['id']);

        $ports = array();
        foreach ($payload['commits'] as $commit) {
            foreach ($commit['added'] as $file) {
                $port = substr($file, 0, strpos($file, '/', strpos($file, '/') + 1));
                if (preg_match('/^([a-zA-Z0-9_+.-]+)\/([a-zA-Z0-9_+.-]+)$/', $port) == 1 && strlen($port) < 100) {
                    $ports[] = $port;
                }
            }

            foreach ($commit['modified'] as $file) {
                $port = substr($file, 0, strpos($file, '/', strpos($file, '/') + 1));
                if (preg_match('/^([a-zA-Z0-9_+.-]+)\/([a-zA-Z0-9_+.-]+)$/', $port) == 1 && strlen($port) < 100) {
                    $ports[] = $port;
                }
            }
        }

        $data = array(
         'commit' => array(
             'id'      => $payload['head_commit']['id'],
             'url'     => $payload['head_commit']['url'],
             'message' => $payload['head_commit']['message'],
             'time'    => $payload['head_commit']['timestamp'],
         ),
         'committer' => array(
             'name'    => $payload['head_commit']['committer']['name'],
             'email'   => $payload['head_commit']['committer']['email'],
         ),
         'repository' => array(
             'url'     => $payload['repository']['url'],
         ),
      );

        $ports = array_unique($ports);

        $jobgroupname = sprintf('github:%s:%s', $payload['repository']['owner']['name'], $payload['repository']['name']);
        $jobgroup = new Jobgroup($jobgroupname);
        $countjobs = $jobgroup->countJobs();

        $jails = new Jails();

        foreach ($config['jails'] as $jail) {
            if (!$jails->exists($jail)) {
                continue;
            }

            $queue = new Queue('preparequeue', $jail);

            foreach ($ports as $port) {
                $data['port'] = $port;
                if ($queue->createJob($data, $jobgroup) !== true) {
                    return false;
                }
            }
        }

        if ($jobgroup->countJobs() > $countjobs) {
            $args = array(
            'jobgroup'   => $jobgroup->getJobgroupId(),
            'repository' => $data['repository']['url'],
            'commit'     => $data['commit']['id'],
            'created'    => time(),
         );

            \Resque::enqueue('default', 'TaskPreparePortstree', $args);
        }

        return true;
    }

    public function _getUserConfig($commitpath)
    {
        $defaultconfig = Config::get('userconfig');

        try {
            $file = @file_get_contents('https://raw.githubusercontent.com/'.$commitpath.'/.redports.json');
            if ($file === false) {
                return $defaultconfig;
            }
        } catch (Exception $e) {
            return $defaultconfig;
        }

        $config = json_decode($file, true);

        foreach ($config as $key => $value) {
            if (isset($defaultconfig[$key])) {
                if (gettype($defaultconfig[$key]) == gettype($value)) {
                    $defaultconfig[$key] = $value;
                } else {
                    if (settype($value, gettype($defaultconfig[$key]))) {
                        $defaultconfig[$key] = $value;
                    }
                }
            }
        }

        return $defaultconfig;
    }
}
