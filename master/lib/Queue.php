<?php

/**
 * Queue for jobs in various jails
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
 */
class Queue
{
   protected $_db;
   protected $_queue;
   protected $_jail;

   function __construct($queue, $jail)
   {
      $this->_db = Config::getDatabaseHandle();
      $this->_queue = $queue;
      $this->_jail = $jail;
   }

   function createJob($data)
   {
      $job = new Job();
      if($job->setJobData($data) !== true)
         return false;

      return $job->save();
   }

   function getFullQueue()
   {
      return $this->_queue.':'.$this->_jail;
   }

   function getNextJob()
   {
      return $this->_db->zRangeByScore($this->getFullQueue(),
         "-inf", "+inf", array('limit' => array(0, 1)));
   }

   function countJobs()
   {
      return $this->_db->zSize($this->getFullQueue());
   }

   function exists()
   {
      return $this->_db->exists($this->getFullQueue());
   }
}

