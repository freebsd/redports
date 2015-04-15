<?php

/**
 * Build job for an individual port on an individual jail
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
 */
class Job
{
   protected $_db;
   protected $_jobid = null;
   protected $_data = null;
   protected $_queues = array('preparequeue', 'waitqueue', 'runqueue', 'archivequeue');

   function __construct($jobid = null)
   {
      $this->_db = Config::getDatabaseHandle();

      $this->_jobid = $jobid;
      $this->_load();
   }

   function exists()
   {
      if($this->_jobid == null)
         return false;

      return $this->_db->exists('jobs:'.$this->_jobid);
   }

   function _load()
   {
      if(!$this->exists())
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
         $this->_db->zAdd($this->getFullQueue(), $this->getPriority(), $this->_jobid);
      }

      return $this->_db->set('jobs:'.$this->_jobid, json_encode($this->_data));
   }

   function getJobId()
   {
      return $this->_jobid;
   }

   function getFullQueue()
   {
      return $this->_data['queue'].':'.$this->_data['jail'];
   }

   function getQueue()
   {
      return $this->_data['queue'];
   }

   function getRepository()
   {
      return $this->_data['repository'];
   }

   function getPriority()
   {
      return $this->_db->zScore($this->getFullQueue(), $this->_jobid);
   }

   function incPriority($inc)
   {
      return $this->_db->zIncrBy($this->getFullQueue(), $inc, $this->_jobid);
   }

   function setPriority($newprio)
   {
      return $this->incPriority($newprio-$this->getPriority());
   }

   function getJobData()
   {
      return $this->_data;
   }

   function setJobData($data)
   {
      if(!isset($data['queue']))
         $data['queue'] = $this->_queues[0];

      if(!isset($data['priority']))
         $data['priority'] = 50;

      if(!isset($data['jail']))
         return false;

      if(!isset($data['repository']))
         return false;

      $this->_data = $data;
      return true;
   }

   function get($field)
   {
      if(isset($this->_data[$field]))
         return $this->_data[$field];

      return null;
   }

   function set($field, $value)
   {
      $this->_data[$field] = $value;
   }

   function moveToQueue($queue)
   {
      if(!in_array($queue, $this->_queues))
      {
         trigger_error('Queuename is invalid', E_USER_ERROR);
         return false;
      }

      $score = $this->getPriority();

      if($this->_db->zRem($this->getFullQueue(), $this->_jobid) !== true)
         return false;

      $this->_data['queue'] = $queue;

      if($this->_db->zAdd($this->getFullQueue(), $score, $this->_jobid) !== true)
         return false;

      return $this->save();
   }
}

