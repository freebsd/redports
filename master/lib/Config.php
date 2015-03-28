<?php

class Config
{
   protected static $settings = array(
      'datasource' => 'sqlite:/var/db/redports/master.db',
      'https_only' => true,
      'userconfig' => array(
          'jails' => array('10.0-RELEASE/amd64', '10.0-RELEASE/i386'),
          'notify' => 'commit' /* commit, email, none */
      )
   );

   public static function get($property)
   {
      if(isset(self::$settings[$property]))
         return self::$settings[$property];

      return false;
   }
}

