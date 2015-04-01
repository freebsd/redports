<?php

class Tasks
{
   protected $_db;

   function __construct($payload = null)
   {
      $this->_db = Config::getDatabaseHandle();

      if(!is_null($payload))
         $this->addTask($payload);
   }

   function addTask($payload)
   {
      $sequ = $this->_db->incr('sequ:tasks');
      $this->_db->set('tasks:'.$sequ, json_encode($payload));
      $this->_db->lPush('alltasks', $sequ);

      return true;
   }

   function getTask()
   {
      if(($sequ = $this->_db->rPop('alltasks')) === false)
         return false;

      if(($data = $this->_db->get('tasks:'.$sequ)) === false)
         return false;

      $this->_db->delete('tasks:'.$sequ);

      return json_decode($data);
   }
}

