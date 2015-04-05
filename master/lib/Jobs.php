<?php

class Jobs
{
   protected $_db;

   function __construct()
   {
      $this->_db = Config::getDatabaseHandle();
   }

   function createJob($data)
   {
      $job = new Job();
      if($job->setJobData($data) !== true)
         return false;

      return $job->save();
   }

   function getNextJob($queue)
   {
      return $this->_db->zRangeByScore($queue, "-inf", "+inf", array('limit' => array(0, 1));
   }

   function countJobs($queue)
   {
      return $this->_db->zSize($queue);
   }
}

