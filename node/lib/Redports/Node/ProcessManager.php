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
   protected $_jails = array();
   protected $_client;
   protected $_log;

   function __construct()
   {
      if(!function_exists('pcntl_fork'))
         trigger_error('pcntl extension not loaded!', E_USER_ERROR);

      $this->_log = Config::getLogger();
      $this->_client = new APIClient();
   }

   function addJail($jail)
   {
      $jailname = $jail->getJailname();

      if(isset($this->_jails[$jailname]))
         return false;

      if(isset($this->_childs[$jailname]))
         return false;

      $this->_childs[$jailname] = 0;
      $this->_jails[$jailname] = $jail;
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
            $this->_log->notice('Got SIGTERM ...');
            $this->stop();
         break;
         case SIGHUP:
            $this->_log->notice('Got SIGHUP ...');
            $this->stop();
         break;
         case SIGINT:
            $this->_log->notice('Got SIGINT ...');
            $this->stop();
         break;
         default:
            $this->_log->warning('Got unknown signal '.$signo);
      }
   }

   function run()
   {
      if(!$this->_client->login())
         return false;

      declare(ticks = 100);

      pcntl_signal(SIGTERM, array($this, 'sighandler'));
      pcntl_signal(SIGHUP, array($this, 'sighandler'));
      pcntl_signal(SIGINT, array($this, 'sighandler'));
      $this->_stop = false;

      while(!$this->_stop)
      {
         foreach($this->_childs as $jailname => $pid)
         {
            if($pid != 0)
               continue;

            $pid = pcntl_fork();
            if($pid == -1)
               trigger_error('Forking failed!!', E_USER_ERROR);

            if($pid)
            {
               /* Parent */
               $this->_childs[$jailname] = $pid;
            }
            else
            {
               /* Child */
               $child = new Child($this->_client, $this->_jails[$jailname]);
               $child->run();

               /* delay to avoid fast respawning */
               sleep(2);

               exit();
            }
         }

         while(true)
         {
            $pid = pcntl_waitpid(-1, $status, WNOHANG);

            if($pid === null || $pid < 1)
               break;

            $jailname = $this->getJailname($pid);
            if($jailname === false)
            {
               $this->_log->error('No jail found for pid '.$pid);
               continue;
            }

            $this->_log->info('child '.$pid.' for '.$jailname.' removed');
            $this->_childs[$jailname] = 0;
         }

         usleep(500000);
      }

      /* wait for childs to exit */
      while($this->countChilds() > 0)
      {
         $this->_log->info('waiting for '.$this->countChilds().' children');
         $pid = pcntl_wait($status);

         $jailname = $this->getJailname($pid);
         $this->_childs[$jailname] = 0;
      }

      return true;
   }
}

