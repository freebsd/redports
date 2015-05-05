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
      'pubkeyhash' => 'ffb4fcbf9223594ec6ab97724cade73446fbdd4cb91f9bd94b74cc79e5bc4aaf',
      'server' => 'https://api.redports.org/',
      'machineid' => '',
      'secret' => ''
   );

   public static function load($file)
   {
      if(!file_exists($file))
         return false;

      $content = file_get_contents($file);
      $json = json_decode($content, true);
      if($json == null)
         return false;

      self::$settings = $json;

      return true;
   }

   public static function get($property)
   {
      if(isset(self::$settings[$property]))
         return self::$settings[$property];

      return false;
   }
}

