<?php

/**
 * Resque Job to prepare a Portstree overlay
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
 */
class Task_PreparePortstree
{
   protected $_db;

   function __construct()
   {
      $this->_db = Config::getDatabaseHandle();
   }

   public function perform()
   {
      echo $this->args['repository'];
   }
}

