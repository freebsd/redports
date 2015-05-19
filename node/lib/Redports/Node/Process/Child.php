<?php

namespace Redports\Node\Process;

use Redports\Node\Config;

/**
 * Child to perform builds for one Poudriere Jail
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich         
 * @license    BSD License (2 Clause)
 * @link       https://freebsd.github.io/redports/
 */
class Child
{
   protected $_client;
   protected $_jail;
   protected $_log;

   function __construct($client, $jail)
   {
      $this->_client = $client;
      $this->_jail = $jail;
      $this->_log = Config::getLogger();
   }

   function run()
   {
      $this->_log->info('polling for job on '.$this->_jail->getJailname());

      $res = $this->_client->takeJob('waitqueue', $this->_jail->getQueue());

      if($res['http_code'] != 200){
         $this->_log->error('Got invalid response from server', $res);
         return false;
      }

      /* TODO: add business logic to poll for jobs and perform them */

      return true;
   }
}

