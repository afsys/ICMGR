<?php

function have_feature($shell_answer)
{
     if (trim($shell_answer) == '')
     {
          return false;
     }
     if (FALSE !== strpos($shell_answer, 'not found'))
     {
          return false;
     }

     if (FALSE !== strpos($shell_answer, 'denied'))
     {
          return false;
     }
     return true;
}

$CheckOptions = array(
  'cron' => have_feature(`crontab -l 2>&1`),
  'fetch'=> have_feature(`fetch 2>&1`),
  'curl' => have_feature(`curl 2>&1`),
  'wget' => have_feature(`wget 2>&1`),
  'lynx' => have_feature(`lynx 2>&1`)
);
var_dump($CheckOptions);
?>
