<?php

namespace Redports\Node\Poudriere;

/**
 * Provides information about a poudriere portstree.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://freebsd.github.io/redports/
 */
class Portstree
{
   const $binpath = '/usr/local/bin/poudriere';

   protected $_portstreename;
   protected $_method;
   protected $_path;
   protected $_updated;

   public function __construct($name)
   {
      $this->_load($name);
   }

   protected function _load($name)
   {
      exec(sprintf("%s ports -l -q", $this->binpath), $output);
  
      foreach($output as $line)
      {
         $parts = preg_split('/\s+/', $line);

         if($parts[0] != $name)
            continue;
 
         $this->_portstreename = $parts[0];
         $this->_method $parts[1];
         $this->_updated = $parts[2].' '.$parts[3];
         $this->_path = $parts[4];

         return true;
      }

      return false;
   }

   function getPortstreename()
   {
      return $this->_portstreename;
   }

   function getMethod()
   {
      return $this->_method;
   }

   function getPath()
   {
      return $this->_path;
   }

   function getUpdated()
   {
      return $this->_updated;
   }
}

