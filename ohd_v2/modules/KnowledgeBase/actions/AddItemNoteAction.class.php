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

if (empty($_GET['item_id']))
{
    die("incorect user item_id");
}

$item_id   = mysql_real_escape_string($_GET['item_id']);
if (!empty($_GET['note_user'])) $note_user = mysql_real_escape_string($_GET['note_user']); else $note_user = '';
if (!empty($_GET['note_text'])) $note_text = mysql_real_escape_string($_GET['note_text']); else $note_text = '';

if ($note_text == '' || $note_user == '')
{
    die("incorect user or text data");
}



//echo "SELECT IFNULL(MAX(note_id)+1,1) FROM kb_items_notes WHERE item_id = $item_id<br>";
$db->exec_query("SELECT IFNULL(MAX(note_id)+1,1) AS id FROM ".DB_PREF."kb_items_notes WHERE item_id = $item_id");
if ($db->error_no != 0) die('error: '.$db->error);
$row = $db->get_data();
$note_id = $row['id'];

    
$query = "
    INSERT INTO ".DB_PREF."kb_items_notes (item_id, note_id, note_user, note_text, note_date)
        VALUES ($item_id, $note_id, '$note_user', '$note_text', NOW())
    ";
//echo $query;
$db->exec_query($query);
if ($db->error_no != 0) die('error: '.$db->error);
die('OK!');