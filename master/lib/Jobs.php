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
}

