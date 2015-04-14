<?php

function isAllowed()
{
   $app = \Slim\Slim::getInstance();

   if(!Session::isAuthenticated()){
      $app->halt(403, 'You are not authenticated');
   }
}

