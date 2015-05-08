<?php

namespace Redports\Node\Poudriere;

/**
 * Provides information about the poudriere installation
 * on this machine.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://freebsd.github.io/redports/
 */
class Poudriere
{
   protected $binpath = '/usr/local/bin/poudriere';

   public function __construct()
   {
      if(!file_exists($this->binpath))
         die($this->binpath.' does not exist!');
   }

   public function getAllJails()
   {
      $jails = array();

      exec(sprintf("%s jail -l -q", $this->binpath), $output);

      foreach($output as $line)
      {
         $parts = preg_split('/\s+/', $line);

         $jails[] = new Jail($parts[0]);
      }

      return $jails;
   }

   public function getAllPortstrees()
   {
      $portstrees = array();

      exec(sprintf("%s ports -l -q", $this->binpath), $output);

      foreach($output as $line)
      {
         $parts = preg_split('/\s+/', $line);

         $portstrees[] = new Portstree($parts[0]);
      }

      return $portstrees;
   }

   public function createJail($name, $version, $arch)
   {
      exec(sprintf("%s jail -c -j %s -v %s -a %s", $name, $version, $arch), $output);

      return $output;
   }

   public function createPortstree($name)
   {
      exec(sprintf("%s ports -c -p %s", $this->binpath, $name), $output);

      return $output;
   }
}

