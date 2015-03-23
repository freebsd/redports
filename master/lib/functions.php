<?php

require_once 'lib/Session.php';
require_once 'lib/Slim/Slim.php';

function isAllowed()
{
   $app = \Slim\Slim::getInstance();

   if(!Session::isAuthenticated()){
      $app->flash('loginerror', 'No permissions.');
      $app->flashKeep();
      $app->redirect('/login');
   }
}

