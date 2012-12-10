<?php

error_reporting(E_ALL);
require_once ('../../../install/FJ/mysql.inc.php');
require_once ('../../../install/db_config.php');
$db = &new db($dbConfig['db'], $dbConfig['host'], $dbConfig['user'], $dbConfig['pass']);

if (empty($_GET['cat_id'])) $_GET['cat_id'] = 'oss';
if (empty($_GET['cat_id'])) die('var cat_items = [];');
$cat_id = $_GET['cat_id'];



// show data
$r = mysql_query("
    SELECT 
      email_id,
      cat_id,
      email_caption,
      email_content
    FROM
      ".DB_PREF."canned_emails
    #WHERE cat_id = '$cat_id'
    ORDER BY
      email_caption
    ") or die("Invalid query: " . mysql_error());



$str = "";
while ($data = mysql_fetch_assoc($r))
{
    $email_content = substr($data['email_content'], 0, 200) . "\n\r...";
//    $email_content = $data['email_content'];
    $str .= "  {id: '". $data['email_id']."', caption: '". addcslashes(nl2br($data['email_caption']), "\0..\37'!@\177..\377") ."', content: '". addcslashes(nl2br($email_content), "\0..\37'!@\177..\377") ."'},\n";
}
if ($str != "") $str = substr($str, 0, strlen($str)-2); 
$str = "var cat_items = [\n". $str ."\n]\n";

echo "".$str;

?>