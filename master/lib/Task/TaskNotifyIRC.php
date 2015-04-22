<?php

/**
 * Resque Job to send build notification to our IRC bridge
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
 */
class TaskNotifyIRC
{
   protected $_db;

   function __construct()
   {
      $this->_db = Config::getDatabaseHandle();
   }

   /**
    *
    * args:
    *  jobid   Job ID
    *  action  Action (started, finished, failed)
   public function perform()
   {
      $token = Config::get('ircbridgetoken');
      $job = new Job($this->args['jobid']);
      $jobdata = $job->getJobData();

      $msg = sprintf('[%%02%s%%0f] (%s) - %s - ', $jobdata['jail'], $jobdata['creator'], $jobdata['port']);

      switch($this->args['action'])
      {
         case 'started':
            $msg .= 'started';
         break;
         case 'finished':
            $msg .= 'finished ('.$jobdata['buildreason'].')';
         break;
         case 'failed':
            $msg .= 'failed';
         break;
         default:
            $msg .= 'unknown';
         break;
      }

      if(file_get_contents("https://redportsircbot-bluelife.rhcloud.com/?token=".$token."&msg=".urlencode($msg)) === false)
         return false;

      return true;
   }
}

