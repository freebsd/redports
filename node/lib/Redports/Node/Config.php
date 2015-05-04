<?php

namespace Redports\Node;

/**
 * Configuration class to store various static settings
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich         
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
 */
class Config
{
   protected static $settings = array(
      'server' => 'https://api.redports.org/',
      'machineid' => 'redbuild99.redports.org',
      'secret' => ''
   );

   public static function get($property)
   {
      if(isset(self::$settings[$property]))
         return self::$settings[$property];

      return false;
   }
}

