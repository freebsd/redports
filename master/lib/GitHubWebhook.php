<?php

class GitHubWebhook
{

   function __construct()
   {
   }

   function handleEvent($event, $payload)
   {
      switch($event)
      {
         case 'ping':
            return array('code' => 200, 'message' => 'pong');
         break;
         case 'push':
             if($this->push(json_decode($payload)))
                return array('code' => 200, 'message' => 'ok');
             else
                return array('code' => 500, 'message' => 'Webhook request failed');
         break;
         default:
            return array('code' => 500, 'message' => 'Event type not implemented');
         break;
      }
   }

   function push($payload)
   {
      $config = $this->getUserConfig($payload['repository']['full_name'].'/'.$payload['commits'][0]['id']);

      $ports = array();
      foreach($payload['commits'] as $commit)
      {
         foreach($commit['added'] as $file)
         {
            $port = strpos($file, '/', strpos($file, '/')+1);
            if(preg_match('^([a-zA-Z0-9_+.-]+)/([a-zA-Z0-9_+.-]+)$', $port) === true && strlen($port) < 100)
               $ports[] = $port;
         }

         foreach($commit['modified'] as $file)
         {
            $port = strpos($file, '/', strpos($file, '/')+1);
            if(preg_match('^([a-zA-Z0-9_+.-]+)/([a-zA-Z0-9_+.-]+)$', $port) === true && strlen($port) < 100)
               $ports[] = $port;
         }
      }

      $data = array(
         'commit' => array(
             'id'      => $payload['head_commit']['id'],
             'url'     => $payload['head_commit']['url'],
             'message' => $payload['head_commit']['message'],
             'time'    => $payload['head_commit']['timestamp']
         ),
         'committer' => array(
             'name'    => $payload['head_commit']['committer']['name'],
             'email'   => $payload['head_commit']['committer']['email']
         ),
         'repository' => array(
             'url'     => $payload['repository']['url']
         )
      );

      $ports = array_unique($ports);
      $jails = $config['jails'];

      return true;
   }

   function _getUserConfig($commitpath)
   {
      $defaultconfig = Config::get('userconfig');

      $file = file_get_contents("https://raw.githubusercontent.com/".$commitpath."/.redports.json");
      if($file === false)
         return $defaultconfig;

      $config = json_decode($file, true);

      foreach($config as $key => $value)
      {
         if(isset($defaultconfig[$key]))
         {
            if(gettype($defaultconfig[$key]) == gettype($value))
               $defaultconfig[$key] = $value;
            else
            {
               if(settype($value, gettype($defaultconfig[$key])))
                  $defaultconfig[$key] = $value;
            }
         }
      }

      return $defaultconfig;
   }
}

