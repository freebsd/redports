<?php

namespace Redports\Node\Command;

use Redports\Node\ProcessManager;

class MainCommand extends Command
{
   function execute($options, $arguments)
   {
      $pm = new ProcessManager();
      $pm->addJail('JAIL1');
      $pm->addJail('JAIL2');

      $pm->run();
   }
}

