<?php

/**
 * Various functions
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
 */

function isAllowed()
{
   $app = \Slim\Slim::getInstance();

   if(!Session::isAuthenticated()){
      $app->halt(403, 'You are not authenticated');
   }
}

