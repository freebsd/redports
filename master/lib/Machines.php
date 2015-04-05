<?php

class Machines
{
   protected $_db;

   function __construct()
   {
      $this->_db = Config::getDatabaseHandle();
   }

   function addMachine($name)
   {
      if($this->_db->sAdd('machines', $name) != 1)
         return false;

      return true;
   }

   function getMachine($name)
   {
      if(!$this->exists($name))
         return false;

      return new Machine($name);
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

