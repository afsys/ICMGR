<?php

require_once ('mysql.inc.php');
require_once ('../db_config.php');

$db = &new db($dbConfig['db'], $dbConfig['host'], $dbConfig['user'], $dbConfig['pass']);
$db-> debug = true;
if (isset($_POST['query']))
{
     $query = get_magic_quotes_gpc()?stripslashes($_POST['query']):$_POST['query'];
     $db-> exec_query($query);
     if ($db-> affected_rows)
     {
          echo 'affected_rows: ', $db-> affected_rows, '<br>';
     }
     echo '<pre>';
     while ($row = $db-> get_data())
     {
          print_r($row);
     }
     echo '</pre>';
}
?>
<form method='POST'>
<textarea name='query' cols=50 rows = 10>
<?echo (string)@htmlspecialchars($query)?>
</textarea><br>
<input type='submit'>;
</form>