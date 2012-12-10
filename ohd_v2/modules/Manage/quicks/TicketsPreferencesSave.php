<?php
header("Content-type: text/javascript");

// http://omni/ohd_new/modules/Manage/quicks/TicketsPreferencesSave.php?status_for_new=New&status_for_closed=Closed


// get sxDB
require_once '../../../install/db_config.php';
require_once '../../../lib/Classes/sx_mysql.class.php';
require_once '../../../lib/Classes/sx_db_ini.class.php';
$db = new sxMySQL(DB_HOST, DB_NAME, DB_USER, DB_PASS);
$dbIni = new sxDbIni($db);

$prefs = array();




// statuses
if (!empty($_GET['statuses']))
{
	$dbIni->removeGroup(DB_PREF.'sys_options', 'ticket_statuses');
	$statuses = explode(",", $_GET['statuses']);
	foreach ($statuses as $k=>$v)
	{
		$prefs['ticket_statuses'][$v] = null;
	}
}

// priorities
if (!empty($_GET['priorities']))
{
	$dbIni->removeGroup(DB_PREF.'sys_options', 'ticket_priorities');
	$priorities = explode(",", $_GET['priorities']);
	foreach ($priorities as $k=>$v)
	{
		$prefs['ticket_priorities'][$v] = null;
	}
}

// types
if (!empty($_GET['types']))
{
	$dbIni->removeGroup(DB_PREF.'sys_options', 'ticket_types');
	$types = explode(",", $_GET['types']);
	foreach ($types as $k=>$v)
	{
		$prefs['ticket_types'][$v] = null;
	}
}

// status for new
if (!empty($_GET['status_for_new']))
{
	if (empty($prefs['tickets'])) $prefs['tickets'] = array();
	$prefs['tickets']['status_for_new'] = $_GET['status_for_new'];
}

// status for closed
if (!empty($_GET['status_for_closed']))
{
	if (empty($prefs['tickets'])) $prefs['tickets'] = array();
	$prefs['tickets']['status_for_closed'] = $_GET['status_for_closed'];
}


//echo "<pre>";
//var_dump($prefs);

// save ini
if (count($prefs) > 0) $dbIni->saveIni(DB_PREF.'sys_options', $prefs);


?>

//alert('Ticket preferences have been save.');
document.getElementById('requestStatus').innerHTML = 'Saved Ok!';
window.setTimeout("document.getElementById('requestStatus').innerHTML = '';", 2000);
document.getElementById('submit_btn').disabled = false;