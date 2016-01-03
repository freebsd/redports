<?php

namespace Redports\Node\Logger;

use Apix\Log\Logger\ErrorLog;
use Apix\Log\Logger\LoggerInterface;
use Psr\Log\InvalidArgumentException;

class Stdout extends ErrorLog implements LoggerInterface
{
    /**
     * Constructor.
     *
     * @param string $file The file to append to.
     *
     * @throws InvalidArgumentException If the file is not writeable.
     */
    public function __construct($file = 'php://stdout')
    {
        if (!file_exists($file) && !touch($file)) {
            throw new InvalidArgumentException(
                sprintf('Log file "%s" cannot be created', $file), 1
            );
        }

        $this->destination = $file;
        $this->type = static::FILE;
    }
}
