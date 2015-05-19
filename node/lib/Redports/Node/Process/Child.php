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

   protected $_job;

   function __construct($client, $jail)
   {
      $this->_client = $client;
      $this->_jail = $jail;
      $this->_log = Config::getLogger();
   }

   function updatePortstree()
   {
      $portstree = $this->_jail->getPortstree();

      if(time()-$portstree->getUpdated() > 60*60*24){
         $this->_log->info('Updating portstree for '.$portstree->getPortstreename());
         $portstree->update();
      }

      return true;
   }

   function getNextJob()
   {
      $res = $this->_client->takeJob('waitqueue', $this->_jail->getQueue());

      if($res['http_code'] != 200){
         $this->_log->error('Got invalid response from server', $res);
         return false;
      }

      $this->_log->info('Got new Job for '.$this->_jail->getJailname());

      $this->_job = json_decode($res['body'], true);
      return true;
   }

   function preparePortstree()
   {
      /* TODO: download overlay, zfs snapshot, apply */
      return false;
   }

   function bulkBuild()
   {
      /* TODO: poudriere bulk */
      return false;
   }

   function uploadBuild()
   {
      /* TODO: get buildresult, upload logfile, finish job */
      return false;
   }

   function cleanupBuild()
   {
      /* TODO: zfs rollback, remove tainted packages */
      return false;
   }

   function run()
   {
      if(!$this->updatePortstree())
         return false;

      if(!$this->getNextJob())
         return false;

      if(!$this->preparePortstree())
         return false;

      $this->bulkBuild() && $this->uploadBuild();

      if(!$this->cleanupBuild())
         return false;

      return true;
   }
}

