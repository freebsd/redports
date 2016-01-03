<?php

namespace Redports\Node\Client;

/**
 * CURL based Connection Manager which is able to send
 * REST API calls.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 *
 * @link       https://freebsd.github.io/redports/
 */
class ConnectionManager
{
    const USERAGENT = 'redports-node/@node_version@';
    const CONNECTTIMEOUT = 3;
    const TIMEOUT = 30;

    protected $_server = null;
    protected $_handle = null;
    protected $_verifypeer = true;

    public function __construct($server)
    {
        if (!function_exists('curl_init')) {
            trigger_error('curl extension not loaded!', E_USER_ERROR);
        }

        $this->_handle = curl_init();
        $this->_setServer($server);
    }

    public function __destruct()
    {
        if ($this->_handle != null) {
            curl_close($this->_handle);
            $this->_handle = null;
        }
    }

    protected function _setServer($server)
    {
        $this->_server = rtrim($server, '/');
        $this->_verifypeer = (substr($server, 0, 5) == 'https');

        return true;
    }

    public function getServer()
    {
        return $this->_server;
    }

    public function call($uri, $method = 'GET', $content = null)
    {
        $url = $this->getServer().'/'.ltrim($uri, '/');

        curl_reset($this->_handle);
        curl_setopt($this->_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->_handle, CURLOPT_HEADER, 1);
        curl_setopt($this->_handle, CURLOPT_URL, $url);
        curl_setopt($this->_handle, CURLOPT_USERAGENT, self::USERAGENT);
        curl_setopt($this->_handle, CURLOPT_CONNECTTIMEOUT, self::CONNECTTIMEOUT);
        curl_setopt($this->_handle, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($this->_handle, CURLOPT_SSL_VERIFYPEER, $this->_verifypeer);
        curl_setopt($this->_handle, CURLOPT_COOKIEFILE, '');
        curl_setopt($this->_handle, CURLINFO_HEADER_OUT, true);

        if ($method == 'GET') {
            curl_setopt($this->_handle, CURLOPT_HTTPGET, 1);
        } elseif ($method == 'POST') {
            curl_setopt($this->_handle, CURLOPT_POST, 1);
            curl_setopt($this->_handle, CURLOPT_POSTFIELDS, $content);
        } elseif ($method == 'PUT') {
            curl_setopt($this->_handle, CURLOPT_PUT, 1);
            curl_setopt($this->_handle, CURLOPT_INFILE, $content);
            curl_setopt($this->_handle, CURLOPT_INFILESIZE, filesize($content));
        } else {
            return false;
        }

        $res = curl_exec($this->_handle);
        if ($res === false) {
            return false;
        }

        $data = array(
         'url'            => curl_getinfo($this->_handle, CURLINFO_EFFECTIVE_URL),
         'http_code'      => curl_getinfo($this->_handle, CURLINFO_HTTP_CODE),
         'total_time'     => curl_getinfo($this->_handle, CURLINFO_TOTAL_TIME),
         'request_header' => explode("\r\n", curl_getinfo($this->_handle, CURLINFO_HEADER_OUT)),
         'header'         => explode("\r\n", substr($res, 0, curl_getinfo($this->_handle, CURLINFO_HEADER_SIZE))),
         'body'           => substr($res, curl_getinfo($this->_handle, CURLINFO_HEADER_SIZE)),
      );

        return $data;
    }
}
