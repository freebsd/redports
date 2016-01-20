<?php

namespace Redports\Master;

/**
 * Stores various user data and an OAuth access token
 * for authentication.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 *
 * @link       https://freebsd.github.io/redports/
 */
class User
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
        if ($this->_db->exists('user:'.$this->_name)) {
            $this->_data = json_decode($this->_db->get('user:'.$this->_name), true);
        }
    }

    public function save()
    {
        $this->_db->set('user:'.$this->_name, json_encode($this->_data));
    }

    public function getUsername()
    {
        return $this->_name;
    }

    public function getOAuthToken()
    {
        return $this->get('token');
    }

    public function get($key, $default = false)
    {
        if(isset($this->_data[$key]))
           return $this->_data[$key];

        return $default;
    }

    public function set($key, $value)
    {
        $this->_data[$key] = $value;
        return true;
    }
}
