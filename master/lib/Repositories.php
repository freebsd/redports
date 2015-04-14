<?php

class Repositories
{
   protected $_db;

   function __construct()
   {
      $this->_db = Config::getDatabaseHandle();
   }

   function addRepository($name, $data)
   {
      if($this->_db->sAdd('repositories', $name) != 1)
         return false;

      $this->_db->set('repositories:'.$name, json_encode($data));

      return true;
   }

   function getRepository($name)
   {
      if(($data = $this->_db->get('repositories:'.$name)) === false)
         return false;

      return json_decode($data, true);
   }

   function deleteRepository($name)
   {
      $this->_db->sRemove('repositories', $name);
      $this->_db->delete('repositories:'.$name);

      return true;
   }

   function exists($name)
   {
      return $this->_db->sIsMember('repositories', $name);
   }
}

