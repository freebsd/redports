<?php

namespace Redports\Web;

/**
 * Configuration class to store various static settings.
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich         
 * @license    BSD License (2 Clause)
 *
 * @link       https://freebsd.github.io/redports/
 */
class Config
{
    protected static $settings = array(
      'https_only'               => true,
      'github.oauth.key'         => '',
      'github.oauth.secret'      => '',
      'github.oauth.redirecturl' => 'https://www.redports.org/login'
   );

    public static function get($property)
    {
        if (isset(self::$settings[$property])) {
            return self::$settings[$property];
        }

        return false;
    }
}

