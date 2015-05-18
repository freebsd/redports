<?php

namespace Redports\Node;

/**
 * Configuration class to store various static settings
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich         
 * @license    BSD License (2 Clause)
 * @link       https://freebsd.github.io/redports/
 */
class Config
{
   protected static $settings = array(
      'manifest' => false,
      'pubkeyhash' => false,
      'server' => false,
      'machineid' => '',
      'secret' => '',
      'logfile' => '/var/log/redports-node.log',
      'loglevel' => 'notice'
   );

   protected static $logger = null;

   public static function load($file)
   {
      if(!file_exists($file))
         return false;

      $content = file_get_contents($file);
      $json = json_decode($content, true);
      if($json == null)
         return false;

      self::$settings = array_merge(self::$settings, $json);

      return true;
   }

   public static function get($property)
   {
      if(isset(self::$settings[$property]))
         return self::$settings[$property];

      return false;
   }

   public static function getLogger()
   {

      if(self::$logger == null)
      {
         $file = new \Apix\Log\Logger\File(self::get('logfile'));
         $file->setMinLevel(self::get('loglevel'));

         $stdout = new \Redports\Node\Logger\Stdout();
         $stdout->setMinLevel(self::get('loglevel'));

         self::$logger = new \Apix\Log\Logger();
         self::$logger->add($file);
         self::$logger->add($stdout);
      }

      return self::$logger;
   }
}

