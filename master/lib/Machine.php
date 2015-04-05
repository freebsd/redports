<?php

class Machine
{
   protected $_db;
   protected $_name;
   protected $_data;

   function __construct($name)
   {
      $this->_db = Config::getDatabaseHandle();
      $this->_name = $name;

      $this->_load();
   }

   function _load()
   {
      $this->_data = json_decode($this->_db->get('machines:'.$this->_name));
   }

   function save()
   {
      $this->_db->set('machines:'.$this->_name, json_encode($this->_data));
   }

   function getName()
   {
      return $this->_name;
   }

   function getToken()
   {
      return $this->_data['token'];
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

