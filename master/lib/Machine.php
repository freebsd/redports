<?php

class Machine
{
   protected $_db;
   protected $_name;

   function __construct($name)
   {
      $this->_db = Config::getDatabaseHandle();
      $this->_name = $name;
   }

   function addJob($jobid)
   {
      if($this->_db->sAdd('machinejobs:'.$this->_name, $jobid) != 1)
         return false;

      return true;
   }

   function releaseJob($jobid)
   {
      if($this->_db->sRemove('machinejobs:'.$this->_name, $jobid) != 1)
         return false;

      return true;
   }

   function getAllJobs()
   {
      return $this->_db->sMembers('machinejobs:'.$this->_name);
   }
}

