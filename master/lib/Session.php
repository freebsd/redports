<?php

class Session
{
   function __construct()
   {
      self::initialize();
   }

   static function initialize()
   {
      // do not expose Cookie value to JavaScript (enforced by browser)
      ini_set('session.cookie_httponly', 1);

      if(Config::get('https_only') === true)
      {
         // only send cookie over https
         ini_set('session.cookie_secure', 1);
      }

      // prevent caching by sending no-cache header
      session_cache_limiter('nocache');

      session_start();
   }

   static function login($secret)
   {
      /* TODO: check if token found in database */
      return false;

      /* login successfull */
      $_SESSION['authenticated'] = true;
      $_SESSION['machineid'] = 0;

      return true;
   }

   static function getMachineId()
   {
      if(isset($_SESSION['machineid']))
         return $_SESSION['machineid'];
      return false;
   }

   static function isAuthenticated()
   {
      return isset($_SESSION['authenticated']);
   }

   static function logout()
   {
      unset($_SESSION['authenticated']);
      unset($_SESSION['machineid']);

      session_destroy();
      return true;
   }
}

