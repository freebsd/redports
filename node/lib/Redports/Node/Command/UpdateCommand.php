<?php

namespace Redports\Node\Command;

use Herrera\Phar\Update\Manager;
use Herrera\Phar\Update\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
   const MANIFEST_URL = 'https://api.redports.org/manifest.json';

   protected function configure()
   {
      $this->setName('update')->setDescription('Updates the application to the latest version');
   }

   protected function execute(InputInterface $input, OutputInterface $output)
   {
      $output->writeln('Checking for updates ...');

      try
      {
         $manager = new Manager(Manifest::loadFile(self::MANIFEST_URL));
      }
      catch (FileException $e)
      {
         $output->writeln('<error>Unable to search for updates</error>');
         return 1;
      }

      if($manager->update($this->getApplication()->getVersion(), true))
         $output->writeln('<info>Updated to latest version</info>');
      else
         $output->writeln('<comment>Already up-to-date</comment>');

      return 0;
   }
}

