<?php

namespace Redports\Node\Command;

use Redports\Node\ProcessManager;

class Command
{
   protected function writeln($line)
   {
      printf("%s\n", $line);
      return true;
   }
}

