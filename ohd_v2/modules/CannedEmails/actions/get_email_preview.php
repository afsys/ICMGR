<?php
    
error_reporting(E_ALL);
require_once ('../../../install/FJ/mysql.inc.php');
require_once ('../../../install/db_config.php');
$db = &new db($dbConfig['db'], $dbConfig['host'], $dbConfig['user'], $dbConfig['pass']);


if (empty($_GET['email_id']))   die('var preview_items = [];');
if (empty($_GET['user_email'])) die('var preview_items = [];');
if (empty($_GET['user_name'])) die('var preview_items = [];');
    
$email_id   = $_GET['email_id'];
$user_email = $_GET['user_email'];
$user_name  = $_GET['user_name'];

// get email
$r = mysql_query("
    SELECT 
      email_id,
      email_caption,
      email_content
    FROM
      ".DB_PREF."canned_emails
    WHERE 
      email_id = $email_id
    ") or die("Invalid query: " . mysql_error());  
$email = mysql_fetch_assoc($r);




$str = "";

$message = $email['email_content'];

// apply template items
$message = str_replace("%CURR_DATE%", date('F d, Y'),  $message);
$message = str_replace("%USER_NAME%", $user_name,      $message);


$str .= "  {user_name: '". $user_email ."', user_email: '". 
        addcslashes($user_email, "\0..\37'!@\177..\377") ."', caption: '". 
        addcslashes(($email['email_caption']), "\0..\37'!@\177..\377") ."', message: '". 
        addcslashes(($message), "\0..\37'!@\177..\377") ."'},\n";

if ($str != "") $str = substr($str, 0, strlen($str)-2); 
$str = "var preview_items = [\n". $str ."\n]\n";

echo "".$str;

?>