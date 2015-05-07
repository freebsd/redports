<?php

namespace Redports\Master;

/**
 * Queue for jobs in various jails
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://freebsd.github.io/redports/
 */
class Queue
{
   protected $_db;
   protected $_queue;
   protected $_jail;
   protected $_queues = array('preparequeue', 'waitqueue', 'runqueue', 'archivequeue');

   function __construct($queue, $jail)
   {
      $this->_db = Config::getDatabaseHandle();

      if(in_array($queue, $this->_queues))
         $this->_queue = $queue;
      else
         $this->_queue = $this->_queues[0];

      $jails = new Jails();
      if($jails->exists($jail))
         $this->_jail = $jail;
      else
         trigger_error(E_USER_ERROR, "Jail is unknown");
   }

   function createJob($data, $jobgroup = null)
   {
      $data['queue'] = $this->_queue;
      $data['jail'] = $this->_jail;

      $job = new Job();
      if($job->setJobData($data) !== true)
         return false;

      if($job->save() !== true)
         return false;

      if($jobgroup == null)
         return true;

      return $jobgroup->addJob($job->getJobId());
   }

   function getFullQueue()
   {
      return $this->_queue.':'.$this->_jail;
   }

   function getNextJob()
   {
      $jobid = $this->_db->zRangeByScore($this->getFullQueue(),
         "-inf", "+inf", array('limit' => array(0, 1)));

      if(count($jobid) < 1)
         return false;

      return new Job($jobid);
   }

   function countJobs()
   {
      return $this->_db->zSize($this->getFullQueue());
   }

   function exists()
   {
      return $this->_db->exists($this->getFullQueue());
   }

   function getQueueInfo()
   {
      if(!$this->exists())
         return false;

      $data = array(
         'numjobs' => $this->countJobs(),
         'jail' => $this->_jail,
         'queue' => $this->_queue
      );

      return $data;
   }
}

