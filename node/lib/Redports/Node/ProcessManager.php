<?php

namespace Redports\Node;

/**
 * Process Manager which runs one child per jailname
 * which does the real work.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://freebsd.github.io/redports/
 */
class ProcessManager
{
   protected $_stop = false;
   protected $_childs = array();

   function __construct()
   {
      if(!function_exists('pcntl_fork'))
         die('pcntl extension not loaded!');
   }

   function addJail($jailname)
   {
      if(isset($this->_childs[$jailname]))
         return false;

      $this->_childs[$jailname] = 0;
      return true;
   }

   function getPid($jailname)
   {
      if(isset($this->_childs[$jailname]))
         return $this->_childs[$jailname];
   }

   function getJailname($pid)
   {
      return array_search($pid, $this->_childs);
   }

   function countChilds()
   {
      $childs = 0;

      foreach($this->_childs as $jail => $pid)
      {
         if($pid > 0)
            $childs++;
      }

      return $childs;
   }

   function stop()
   {
      $this->_stop = true;
   }

   function sighandler($signo)
   {
      switch($signo)
      {
         case SIGTERM:
            echo "Got SIGTERM ...\n";
            $this->stop();
         break;
         case SIGHUP:
            echo "Got SIGHUP ...\n";
            $this->stop();
         break;
         case SIGINT:
            echo "Got SIGINT ...\n";
            $this->stop();
         break;
         default:
            echo "Got unknown signal ".$signo."\n";
      }
   }

   function run()
   {
      declare(ticks = 100);

      pcntl_signal(SIGTERM, array($this, 'sighandler'));
      pcntl_signal(SIGHUP, array($this, 'sighandler'));
      pcntl_signal(SIGINT, array($this, 'sighandler'));
      $this->_stop = false;

      while(!$this->_stop)
      {
         foreach($this->_childs as $jail => $pid)
         {
            if($pid != 0)
               continue;

            $pid = pcntl_fork();
            if($pid == -1)
               die('Forking failed!!');

            if($pid)
            {
               /* Parent */
               $this->_childs[$jail] = $pid;
            }
            else
            {
               /* Child */
               echo "child ".getmypid()." for ".$jail." started\n";
               sleep(rand(2, 10));
               echo "child ".getmypid()." for ".$jail." ended\n";
               exit();
            }
         }

         while(true)
         {
            $pid = pcntl_waitpid(-1, $status, WNOHANG);

            if($pid === null || $pid < 1)
               break;

            $jail = $this->getJailname($pid);
            if($jail === false)
            {
               echo "No jail found for pid ".$pid."\n";
               continue;
            }

            echo "child ".$pid." for ".$jail." removed\n";
            $this->_childs[$jail] = 0;
         }

         usleep(500000);
      }

      /* wait for childs to exit */
      while($this->countChilds() > 0)
      {
         echo "waiting for ".$this->countChilds()." children\n";
         $pid = pcntl_wait($status);

         $jail = $this->getJailname($pid);
         $this->_childs[$jail] = 0;
      }

      return true;
   }
}

