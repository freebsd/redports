<?php

namespace Redports\Node\Command;

use Redports\Node\ProcessManager;
use Redports\Node\Poudriere\Poudriere;

class MainCommand extends Command
{
   function execute($options, $arguments)
   {
      $pm = new ProcessManager();
      $poudriere = new Poudriere();

      foreach($poudriere->getAllJails() as $jail)
         $pm->addJail($jail->getJailname());

      $pm->run();
   }
}

