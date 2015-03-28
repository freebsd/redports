<?php

function handleGitHubPushEvent($payload)
{
   $config = getUserConfig($payload['repository']['full_name'].'/'.$payload['commits'][0]['id']);
}

function getUserConfig($commitpath)
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

