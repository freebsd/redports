<?php

/**
 * Process Manager which runs one child per jailname
 * which does the real work.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
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
      if(!isset($this->_childs[$jailname]))
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
      return array_search($this->_childs, $pid);
   }

   function stop()
   {
      $this->_stop = true;
   }

   function run()
   {
      static $loops = 3;

      if(!$this->_stop)
      {
         echo "checking\n";

         foreach($this->_childs as $jail => $pid)
         {
            if($pid != 0)
               continue;

            if($loops-- < 1)
            {
               echo "Stopping ...\n";
               $this->stop();
            }

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
               sleep(rand(10, 20));
               echo "child ".getmypid()." for ".$jail." ended\n";
               exit();
            }
         }
      }

      while(true)
      {
         $pid = pcntl_waitpid(-1, $status, WNOHANG);

         if($pid === null || $pid == -1)
            break;

         $jail = $this->getJailname($pid);
         if($jail === false)
            die('What the hell');

         echo "child ".$pid." for ".$jail." removed\n";
         $this->_childs[$jail] = 0;
      }

      usleep(500000);
   }
}

