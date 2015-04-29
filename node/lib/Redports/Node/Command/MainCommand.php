<?php

namespace Redports\Node\Command;

use Redports\Node\ProcessManager;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MainCommand extends Command
{
   protected function configure()
   {
      $this->setName('run')->setDescription('Run the application');
   }

   protected function execute(InputInterface $input, OutputInterface $output)
   {
      $pm = new ProcessManager();
      $pm->addJail('JAIL1');
      $pm->addJail('JAIL2');

      $pm->run();
   }
}

