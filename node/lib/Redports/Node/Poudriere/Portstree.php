<?php

namespace Redports\Node\Poudriere;

/**
 * Provides information about a poudriere portstree.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 *
 * @link       https://freebsd.github.io/redports/
 */
class Portstree
{
    protected $binpath = '/usr/local/bin/poudriere';

    protected $_portstreename;
    protected $_method;
    protected $_path;
    protected $_updated;

    public function __construct($name)
    {
        $this->_load($name);
    }

    protected function _load($name)
    {
        exec(sprintf('%s ports -l -q', $this->binpath), $output, $result);

        if ($result != 0) {
            return false;
        }

        foreach ($output as $line) {
            $parts = preg_split('/\s+/', $line);

            if ($parts[0] != $name) {
                continue;
            }

            $this->_portstreename = $parts[0];
            $this->_method = $parts[1];
            $this->_updated = $parts[2].' '.$parts[3];
            $this->_path = $parts[4];

            return true;
        }

        return false;
    }

    public function update()
    {
        exec(sprintf('%s ports -u -p %s', $this->binpath, $this->portstreename));

        return true;
    }

    public function getPortstreename()
    {
        return $this->_portstreename;
    }

    public function getMethod()
    {
        return $this->_method;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function getUpdated($raw = false)
    {
        if ($raw) {
            return $this->_updated;
        }

        return strtotime($this->_updated);
    }
}
