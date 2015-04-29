<?php

namespace Redports\Master\Task;

/**
 * Resque Job to send a build notification via email
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://decke.github.io/redports/
 */
class TaskNotifyEMail
{
   protected $_db;
   protected $_headers = array(
      'From: "redports.org" <noreply@redports.org>',
      'Reply-To: "redports.org" <noreply@redports.org>',
      'Content-type: text/plain; charset=iso-8859-1'
   );

   function __construct()
   {
      $this->_db = Config::getDatabaseHandle();
   }

   public function perform()
   {
      $job = new Job($this->args['jobid']);
      $jobdata = $job->getJobData();

      $to = '';
      $subject = 'Build '.$this->args['action'];
      $content = 'Build '.$this->args['action'];

      return mail($to, $subject, $content, implode("\r\n", $this->_headers));
   }
}

