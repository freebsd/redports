<?php

namespace Redports\Node\Command;

class Command
{
   function __construct()
   {
      ;
   }

   function execute($options, $arguments, $app = null)
   {
      if($options['update'])
         $cmd = new UpdateCommand();
      else if($options['setup'])
         $cmd = new SetupCommand();
      else
         $cmd = new MainCommand();

      return $cmd->execute($options, $arguments, $app);
   }

   protected function writeln($line)
   {
      printf("%s\n", $line);
      return true;
   }
}

