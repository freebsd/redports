<?php

class Jobgroup
{
   protected $_db;
   protected $_groupid;

   function __construct($groupid)
   {
      $this->_db = Config::getDatabaseHandle();
      $this->_groupid = $groupid;
   }

   function getJobgroupId()
   {
      return $this->_groupid;
   }

   function addJob($jobid)
   {
      if(!$this->_db->exists('jobs:'.$jobid))
         return false;

      if($this->_db->sAdd('jobgroup:'.$this->getJobgroupId(), $jobid) != 1)
         return false;

      return true;
   }

   function getJobs()
   {
      return $this->_db->sMembers('jobgroup:'.$this->getJobgroupId());
   }

   function deleteJobgroup()
   {
      $this->_db->delete('jobgroup:'.$this->getJobgroupId());

      return true;
   }

   function exists($groupid = null)
   {
      if(is_null($groupid))
         $groupid = $this->_groupid;

      return $this->_db->exists('jobgroup:'.$groupid);
   }
}

