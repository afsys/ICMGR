<?PHP
    
/**
 * AddItemNoteAction class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    27 September 2005
 */
    
//require '../../../install/db_config.php';

error_reporting(E_ALL);
require_once ('../../../install/FJ/mysql.inc.php');
require_once ('../../../install/db_config.php');
$db = &new db($dbConfig['db'], $dbConfig['host'], $dbConfig['user'], $dbConfig['pass']);

if (empty($_GET['item_id']))    die("incorect user item_id");
if (empty($_GET['user_ip']))    die("incorect user user_ip");
if (empty($_GET['rait_value'])) die("incorect user rait_value");


$item_id     = ip2long($_GET['item_id']);
$user_ip     = mysql_real_escape_string($_GET['user_ip']);
$rait_value  = mysql_real_escape_string($_GET['rait_value']);


$db->exec_query("SELECT IFNULL(MAX(rait_id)+1,1) AS id FROM ".DB_PREF."kb_items_raiting WHERE item_id = $item_id");
if ($db->error_no != 0) die('error: '.$db->error);
$row = $db->get_data();
$rait_id = $row['id'];

    
$query = "
    INSERT INTO ".DB_PREF."kb_items_raiting (item_id, rait_id, rait_value, rait_user_ip, rait_note_date)
        VALUES ($item_id, $rait_id, '$rait_value', '$user_ip', NOW())
    ";
//echo $query;
$db->exec_query($query);
if ($db->error_no != 0) die('error: '.$db->error);
die('OK!');