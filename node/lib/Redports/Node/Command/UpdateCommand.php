<?php

namespace Redports\Node\Command;

use Redports\Node\Config;
use Redports\Node\UpdateManager;
use Herrera\Phar\Update\Manifest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{
   const MANIFEST_URL = 'https://api.redports.org/downloads/manifest.json';

   protected function configure()
   {
      $this->setName('update')->setDescription('Updates the application to the latest version');
   }

   protected function execute(InputInterface $input, OutputInterface $output)
   {
      $output->writeln('Checking for updates ...');

      try
      {
         $manager = new UpdateManager(Manifest::loadFile(self::MANIFEST_URL));

         if (Config::get('pubkeyhash') !== false)
            $manager->setPublicKeyHash(Config::get('pubkeyhash'));
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

