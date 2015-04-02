<?php

class Job
{
   protected $_db;
   protected $_jobid = null;
   protected $_data = null;
   protected $_queues = array('newqueue', 'preparequeue', 'waitqueue', 'runqueue', 'archivequeue');

   function __construct($jobid = null)
   {
      $this->_db = Config::getDatabaseHandle();

      $this->_jobid = $jobid;
      $this->_load();
   }

   function _load()
   {
      if($this->_jobid == null)
         return false;

      if(($this->_data = $this->_db->get('jobs:'.$this->_jobid)) === false)
         return false;
      return true;
   }

   function save()
   {
      if($this->_jobid == null)
      {
         $this->_jobid = $this->_db->incr('sequ:jobs');

         $this->_db->sAdd('alljobs', $this->_jobid);
         $this->_db->sAdd('repojobs:'.$this->getRepository(), $this->_jobid);
         $this->_db->sAdd($this->getQueue(), $this->_jobid);
      }

      return $this->_db->set('jobs:'.$this->_jobid, json_encode($this->_data));
   }

   function getJobId()
   {
      return $this->_jobid;
   }

   function getQueue()
   {
      return $this->_data['queue'];
   }

   function getRepository()
   {
      return $this->_data['repository'];
   }

   function getJobData()
   {
      return $this->_data;
   }

   function setJobData($data)
   {
      if(!isset($data['queue']))
         $data['queue'] = $this->_queues[0];

      if(!isset($data['repository']))
         return false;

      $this->_data = $data;
      return true;
   }

   function moveToQueue($queue)
   {
      if(!in_array($queue, $this->_queues))
      {
         trigger_error('Queuename is invalid', E_USER_ERROR);
         return false;
      }

      if($this->_db->sMove($this->getQueue(), $queue, $this->getJobId()) !== true)
         return false;

      $this->_data['queue'] = $queue;

      return $this->save();
   }
}

