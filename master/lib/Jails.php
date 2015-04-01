<?php

class Jails
{
   protected $_db;

   function __construct()
   {
      $this->_db = Config::getDatabaseHandle();
   }

   function addJail($name, $data)
   {
      if($this->_db->sAdd('jails', $name) != 1)
         return false;

      $this->_db->set('jails:'.$name, json_encode($data));

      return true;
   }

   function getJail($name)
   {
      if(($data = $this->_db->get('jails:'.$name)) === false)
         return false;

      return json_decode($data);
   }

   function deleteJail($name)
   {
      $this->_db->sRemove('jails', $name);
      $this->_db->delete('jails:'.$name);

      return true;
   }

   function exists($name)
   {
      return $this->_db->sIsMember('jails', $name);
   }
}

