<?php

function handleGitHubPushEvent($payload)
{
   $config = getUserConfig($payload['repository']['full_name'].'/'.$payload['commits'][0]['id']);
}

function getUserConfig($commitpath)
{
   $defaultconfig = Config::get('userconfig');

   $config = yaml_parse_url("https://raw.githubusercontent.com/".$commitpath."/.redports.yml");
   if($config === false)
      return $defaultconfig;

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

