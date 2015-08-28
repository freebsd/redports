<?php

namespace Redports\Master\Task;

/**
 * Resque Job to prepare a Portstree overlay
 *
 * @author     Bernhard Froehlich <decke@bluelife.at>
 * @copyright  2015 Bernhard Froehlich
 * @license    BSD License (2 Clause)
 * @link       https://freebsd.github.io/redports/
 */
class TaskPreparePortstree
{
   protected $_db;

   function __construct()
   {
      $this->_db = Config::getDatabaseHandle();
   }

   function tempdir($prefix)
   {
      $tmpfile = tempnam(sys_get_temp_dir(), $prefix);

      if(file_exists($tmpfile))
         unlink($tmpfile);

      mkdir($tmpfile);

      if(is_dir($tmpfile))
         return $tmpfile;

      return null;
   }

   protected function downloadFile($url, $file)
   {
      $fr = fopen($url, "r");
      $fw = fopen($file, "w");

      if(stream_copy_to_stream($fr, $fw) < 1)
         return null;

      fclose($fw);
      fclose($fr);

      return true;
   }

   protected function setArchiveForJobgroup($jobgroupid, $uri)
   {
      $jobgroup = new Jobgroup($jobgroupid);

      foreach($jobgroup->getJobs() as $jobid)
      {
         $job = new Job($jobid);
         if($job->set('portsoverlay', $uri) !== true)
            return false;

         if($job->moveToQueue('waitqueue') !== true)
            return false;
      }

      return true;
   }

   public function perform()
   {
      $targetdir = Config::get('logdir').'/'.$this->args['jobgroup'].'/';
      $targeturi = sprintf('%s/%s/portstree-%s.tar.xz', basename(Config::get('logdir')), $this->args['jobgroup'], $this->args['commit']);
      $targetfile = sprintf('%s/%s', $targetdir, basename($targeturi));

      $tmpdir = $this->tempdir("php-");
      $tmpfile = $tmpdir.'/portstree.tar.gz';

      if(file_exists($targetfile))
         return $this->setArchiveForJobgroup($this->args['jobgroup'], $targeturi);

      if($this->downloadFile($this->args['repository']."/archive/".$this->args['commit'].".tar.gz", $tmpfile) !== true)
         return false;

      chdir($tmpdir);

      mkdir($tmpdir.'/ports/');
      exec("/usr/bin/tar xf ".$tmpfile." -C ".$tmpdir."/ports/ --strip-components 1");
      unlink($tmpfile);

      exec("rm -rf ".$tmpdir."/ports/Mk");

      if(!file_exists($targetdir))
         mkdir($targetdir);

      exec("/usr/bin/tar cfJ ".$targetfile." ports");

      exec("rm -rf ".$tmpdir);

      if($this->setArchiveForJobgroup($this->args['jobgroup'], $targeturi) !== true)
         return false;

      return true;
   }
}

