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

function jsonResponse($code, $data)
{
   $app = \Slim\Slim::getInstance();
   $app->response->setStatus($code);
   $app->response->headers->set('Content-Type', 'application/json');
   $app->response->write(json_encode($data));

   if($code != 200)
      $app->stop();

   return true;
}

function textResponse($code, $data)
{
   $app = \Slim\Slim::getInstance();
   $app->response->setStatus($code);
   $app->response->headers->set('Content-Type', 'text/plain');
   $app->response->write($data);

   if($code != 200)
      $app->stop();

   return true;
}

