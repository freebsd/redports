<?php

namespace Redports\Node\Command;

use Redports\Node\Poudriere\Poudriere;
use Redports\Node\Poudriere\Jail;

class SetupCommand extends Command
{
   function showSetup()
   {
      $poudriere = new Poudriere();

      $this->writeln(sprintf("%-25s%-25s", "POUDRIERE JAIL", "REDPORTS QUEUE"));

      foreach($poudriere->getAllJails() as $jail)
      {
         $queue = $jail->getQueue() ? $jail->getQueue() : '-';

         $this->writeln(sprintf("%-25s%-25s", $jail->getJailname(), $queue));
      }

      return 0;
   }

   function modifyJail($jailname, $queue)
   {
      $jail = new Jail($jailname);

      if(strlen($queue) < 1 || $queue == 'none')
         $jail->unsetQueue();
      else
         $jail->setQueue($queue);

      return true;
   }

   function execute($options, $arguments)
   {
      if(count($arguments) < 3)
         $this->showSetup();
      else
      {
         array_shift($arguments);
         array_shift($arguments);
         
         foreach($arguments as $tmp)
         {
            list($jail, $queue) = array_merge(explode('=', $tmp, 2), array('none'));
            $this->modifyJail($jail, $queue);
         }
      }

      return 1;
   }
}

