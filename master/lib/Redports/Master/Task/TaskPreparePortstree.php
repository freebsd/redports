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

   public function perform()
   {
      $tmpdir = $this->tempdir("php-");
      $tmpfile = $tmpdir.'/portstree.tar.gz';

      if($this->downloadFile($this->args['repository']."/archive/".$this->args['commit'].".tar.gz", $tmpfile) !== true)
         return false;

      chdir($tmpdir);

      mkdir($tmpdir.'/ports/');
      exec("/usr/bin/tar xf ".$tmpfile." -C ".$tmpdir."/ports/ --strip-components 1");
      unlink($tmpfile);

      exec("rm -rf ".$tmpdir."/ports/Mk");

      $targetdir = Config::get('logdir').'/'.$this->args['jobgroup'].'/';
      $targetfile = $targetdir.'/portstree-'.$this->args['commit'].'.tar.xz';

      if(!file_exists($targetdir))
         mkdir($targetdir);

      exec("/usr/bin/tar cfJ ".$targetfile." ports");

      exec("rm -rf ".$tmpdir);

      // TODO: update jobs to set link to portstreefile
      // TODO: move jobs to next queue

      return true;
   }
}

