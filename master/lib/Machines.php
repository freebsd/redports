<?php

class Machines
{
   protected $_db;

   function __construct()
   {
      $this->_db = Config::getDatabaseHandle();
   }

   function addMachine($name, $data)
   {
      if($this->_db->sAdd('machines', $name) != 1)
         return false;

      $this->_db->set('machines:'.$name, json_encode($data));

      return true;
   }

   function getMachine($name)
   {
      if(($data = $this->_db->get('machines:'.$name)) === false)
         return false;

      return json_decode($data);
   }

   function deleteMachine($name)
   {
      $this->_db->sRemove('machines', $name);
      $this->_db->delete('machines:'.$name);

      return true;
   }

   function exists($name)
   {
      return $this->_db->sIsMember('machines', $name);
   }
}

