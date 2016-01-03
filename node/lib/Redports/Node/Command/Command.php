<?php

namespace Redports\Node\Command;

class Command
{
    public function __construct()
    {
        ;
    }

    public function execute($options, $arguments, $app = null)
    {
        if ($options['update']) {
            $cmd = new UpdateCommand();
        } elseif ($options['setup']) {
            $cmd = new SetupCommand();
        } else {
            $cmd = new MainCommand();
        }

        return $cmd->execute($options, $arguments, $app);
    }

    protected function writeln($line)
    {
        printf("%s\n", $line);

        return true;
    }
}
