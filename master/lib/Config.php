<?php

class Config
{
   protected static $settings = array(
      'datasource' => 'sqlite:/var/db/redports/master.db',
      'https_only' => true
   );

   public static function get($property)
   {
      if(isset(self::$settings[$property]))
         return self::$settings[$property];

      return false;
   }
}

