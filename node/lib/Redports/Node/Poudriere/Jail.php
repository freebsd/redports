<?php

namespace Redports\Node\Poudriere;

/**
 * Provides information about a poudriere Jail.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://freebsd.github.io/redports/
 */
class Jail
{
   protected $binpath = '/usr/local/bin/poudriere';

   protected $_jailname;
   protected $_version;
   protected $_arch;
   protected $_method;
   protected $_path;
   protected $_fs;
   protected $_updated;
   protected $_queue;

   public function __construct($jailname)
   {
      $this->_load($jailname);
   }

   protected function _load($jailname)
   {
      exec(sprintf("%s jail -i -j %s", $this->binpath, $jailname), $output, $retval);

      if($retval != 0 || count($output) < 1)
         return false;

      foreach($output as $line)
      {
         $parts = explode(':', $line, 2);
         $tmp = explode(' ', $parts[0], 2);
         
         $key = trim($tmp[1]);
         $value = trim($parts[1]);

         switch($key)
         {
            case 'name':
               $this->_jailname = $value;
            break;
            case 'version':
               $this->_version = $value;
            break;
            case 'arch':
               $this->_arch = $value;
            break;
            case 'method':
               $this->_method = $value;
            break;
            case 'mount':
               $this->_path = $value;
            break;
            case 'fs':
               $this->_fs = $value;
            break;
            case 'updated':
               $this->_updated = $value;
            break;
         }
      }

      unset($output);
      exec(sprintf("zfs get -o value redports:queue %s", $this->_fs), $output, $retval);

      if($retval == 0 && count($output) == 2)
      {
         $value = trim($output[1]);

         if($value != '-' && $value != 'none')
            $this->_queue = $value;
      }

      return true;
   }

   function setQueue($queue)
   {
      if(!$this->_fs)
         return false;

      exec(sprintf("zfs set redports:queue=%s %s", $queue, $this->_fs));
      return true;
   }

   function unsetQueue()
   {
      if(!$this->_fs)
         return false;

      exec(sprintf("zfs inherit -Sr redports:queue %s", $this->_fs));
      return true;
   }

   function getJailname()
   {
      return $this->_jailname;
   }

   function getVersion()
   {
      return $this->_version;
   }

   function getArch()
   {
      return $this->_arch;
   }

   function getMethod()
   {
      return $this->_method;
   }

   function getPath()
   {
      return $this->_path;
   }

   function getFilesystem()
   {
      return $this->_fs;
   }

   function getUpdated()
   {
      return $this->_updated;
   }

   function getQueue()
   {
      return $this->_queue;
   }
}

