<?php

namespace Redports\Master;

/**
 * Stores various settings of a client machine including
 * a token and credentials for authentication.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 *
 * @link       https://freebsd.github.io/redports/
 */
class Machine
{
    protected $_db;
    protected $_name;
    protected $_data = array();

    public function __construct($name)
    {
        $this->_db = Config::getDatabaseHandle();
        $this->_name = $name;

        $this->_load();
    }

    public function _load()
    {
        if ($this->_db->exists('machines:'.$this->_name)) {
            $this->_data = json_decode($this->_db->get('machines:'.$this->_name), true);
        } else {
            $this->_data['token'] = $this->generateToken();
        }
    }

    public function save()
    {
        $this->_db->set('machines:'.$this->_name, json_encode($this->_data));
    }

    public function getName()
    {
        return $this->_name;
    }

    public function getToken()
    {
        return $this->_data['token'];
    }

    public function addJob($jobid)
    {
        if ($this->_db->sAdd('machinejobs:'.$this->_name, $jobid) != 1) {
            return false;
        }

        return true;
    }

    public function releaseJob($jobid)
    {
        if ($this->_db->sRemove('machinejobs:'.$this->_name, $jobid) != 1) {
            return false;
        }

        return true;
    }

    public function hasJob($jobid)
    {
        return $this->_db->sIsMember('machinejobs:'.$this->_name, $jobid);
    }

    public function getAllJobs()
    {
        return $this->_db->sMembers('machinejobs:'.$this->_name);
    }

    protected function generateToken()
    {
        $cstrong = true;
        $bytes = '';

        for ($i = 0; $i <= 32; $i++) {
            $bytes .= bin2hex(openssl_random_pseudo_bytes(8, $cstrong));
        }

        return hash('sha256', $bytes);
    }
}
