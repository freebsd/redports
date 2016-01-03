<?php

namespace Redports\Master;

/**
 * Class to store information about our users repositories.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 *
 * @link       https://freebsd.github.io/redports/
 */
class Repositories
{
    protected $_db;

    public function __construct()
    {
        $this->_db = Config::getDatabaseHandle();
    }

    public function addRepository($name, $data)
    {
        if ($this->_db->sAdd('repositories', $name) != 1) {
            return false;
        }

        $this->_db->set('repositories:'.$name, json_encode($data));

        return true;
    }

    public function getRepository($name)
    {
        if (($data = $this->_db->get('repositories:'.$name)) === false) {
            return false;
        }

        return json_decode($data, true);
    }

    public function deleteRepository($name)
    {
        $this->_db->sRemove('repositories', $name);
        $this->_db->delete('repositories:'.$name);

        return true;
    }

    public function exists($name)
    {
        return $this->_db->sIsMember('repositories', $name);
    }
}
