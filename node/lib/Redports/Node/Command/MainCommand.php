<?php

namespace Redports\Node\Command;

use Redports\Node\Config;
use Redports\Node\Poudriere\Poudriere;
use Redports\Node\Process\ProcessManager;

class MainCommand extends Command
{
    public function execute($options, $arguments)
    {
        $logger = Config::getLogger();
        $pm = new ProcessManager();
        $poudriere = new Poudriere();

        foreach ($poudriere->getAllJails() as $jail) {
            if ($jail->getQueue() === null) {
                $logger->warning('Ignoring Jail '.$jail->getJailname().' because Queue is not defined');
            } else {
                $logger->info('Adding Jail '.$jail->getJailname().' to Queue '.$jail->getQueue());
                $pm->addJail($jail);
            }
        }

        $pm->run();
    }
}
