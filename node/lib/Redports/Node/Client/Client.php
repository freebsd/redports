<?php

namespace Redports\Node\Client;

use Redports\Node\Config;

/**
 * REST API client for Redports web api.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 *
 * @link       https://freebsd.github.io/redports/
 */
class Client
{
    protected $_conn = null;

    public function __construct($server = null)
    {
        if ($server == null) {
            $server = Config::get('server');
        }

        $this->_conn = new ConnectionManager($server);
    }

    public function login($machineid = null, $secret = null)
    {
        if ($machineid == null) {
            $machineid = Config::get('machineid');
        }

        if ($secret == null) {
            $secret = Config::get('secret');
        }

        return $this->_conn->call('/auth/', 'POST', 'machineid='.urlencode($machineid).'&secret='.urlencode($secret));
    }

    public function listJails()
    {
        return $this->_conn->call('/jails/');
    }

    public function takeJob($queue, $jail)
    {
        return $this->_conn->call('/queues/'.urlencode($queue).'/'.urlencode($jail).'/take');
    }

    public function uploadLog($jobid, $file)
    {
        return $this->_conn->call('/jobs/'.$jobid.'/logfile/'.urlencode(basename($file)), 'PUT', $file);
    }
}
