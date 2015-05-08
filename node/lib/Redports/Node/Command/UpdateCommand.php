<?php

namespace Redports\Node\Command;

use Redports\Node\Config;
use Redports\Node\UpdateManager;
use Herrera\Phar\Update\Manifest;

class UpdateCommand extends Command
{
   function execute($options, $arguments, $app)
   {
      $manifest = Config::get('manifest');

      if($manifest === false)
      {
         $this->writeln('Manifest URL not defined');
         return 1;
      }

      $this->writeln('Checking for updates ...');

      try
      {
         $manager = new UpdateManager(Manifest::loadFile($manifest));

         if (Config::get('pubkeyhash') !== false)
            $manager->setPublicKeyHash(Config::get('pubkeyhash'));
      }
      catch (FileException $e)
      {
         $this->writeln('Unable to search for updates');
         return 1;
      }

      if($manager->update($app->version, true))
         $this->writeln('Updated to latest version');
      else
         $this->writeln('Already up-to-date');

      return 0;
   }
}

