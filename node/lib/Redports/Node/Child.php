<?php

namespace Redports\Node;

/**
 * Child to perform builds for one Poudriere Jail
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich         
 * @license    BSD License (2 Clause)
 * @link       https://freebsd.github.io/redports/
 */
class Child
{
   protected $_client;
   protected $_jailname;

   function __construct($client, $jailname)
   {
      $this->_client = $client;
      $this->_jailname = $jailname;
   }

   function run()
   {
      echo "polling for job on ".$this->_jailname."\n";

      /* TODO: add business logic to poll for jobs and perform them */

      sleep(1);

      return false;
   }
}

