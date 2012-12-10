<?php
	
session_save_path('./../../../_tmp/sess');
session_start();

require_once "../../../config.php";
require_once "../../../lib/xajax.inc.php";
require_once "../../../lib/Classes/sx_db_ini.class.php";  
require_once "../../../lib/Classes/products.class.php";
require_once "../../../lib/Classes/users.class.php";
require_once "../../../lib/Classes/livechat.class.php";

require_once "../../../install/version.php";
require_once "../../../lib/SmartyRendererStandAlone.class.php";

	
	
function alert()
{
	$objResponse = new xajaxResponse();
	$objResponse->addAlert("123z");
	return $objResponse;
}

function SendMessage($user_id, $user_key, $message, $params)
{
	$objResponse = new xajaxResponse();

	if (LiveChat::AddMessage($user_id, $user_key, $message)) {
		$objResponse->addScript("SendMessage_Res('". date(LC_DATE_FORMAT) ."', true);");
	}
	else {
		$objResponse->addScript("SendMessage_Res('". date(LC_DATE_FORMAT) ."', false, 'Failed to post message!')");
	}
	return $objResponse;
}

function GetOponentMessages($user_id, $user_key, $sid, $messages_from = null, $write_activity = 'NULL')
{
	//var_dump($_SESSION['livechat']);
	
	$write_activity = (is_numeric($write_activity) && $write_activity > 0) ? $write_activity : 'NULL';
	
	$opponent_data = LiveChat::GetUserData($_SESSION['livechat']['opponent_data']['user_id']);

	// get opponent active status
	if (!empty($_SESSION['livechat']['opponent_data']['user_id'])) {
		if (!$opponent_data['is_active']) {
			LiveChat::AddMessage($_SESSION['livechat']['opponent_data']['user_id'], $_SESSION['livechat']['opponent_data']['user_key'], 
			                     'ping timeout (probably user closed browser window)...', null, LCM_OPPONENT_PING_TIMEOUT);
			$_SESSION['livechat']['opponent_data']['status'] = LCM_OPPONENT_PING_TIMEOUT;
		}
	}
	
	// on ping in after ping time out
	if (!empty($_SESSION['livechat']['opponent_data']['status']) &&  $_SESSION['livechat']['opponent_data']['status'] == LCM_OPPONENT_PING_TIMEOUT &&
		       $opponent_data['is_active']) {
		       
		LiveChat::AddMessage($_SESSION['livechat']['opponent_data']['user_id'], $_SESSION['livechat']['opponent_data']['user_key'], 
		                     'user again is active...', null, LCM_OPPONENT_PING_TIMEIN);
		unset($_SESSION['livechat']['opponent_data']['status']);
	}
	
	$objResponse = new xajaxResponse();
	$messages = LiveChat::GetOpponentMessages($user_id, $sid, $messages_from);
	
	/* var_dump($messages);
	return $objResponse; */

	
	$res_str = '';
	$last_rec_time = null;
	
	/* $messages = array (array(
		'conversation_id' => 'skip',
		'user_nickname'   => 'test user',
		'message'         => 'message',
		'rec_time'        => 'rec time',
		'service_command' => LCM_OPPONENT_LOGGED_IN
	)); /**/
	
	// mark just gotten messages
	LiveChat::MarkMessagesAsReaded($_SESSION['livechat']['user_data']['user_id'], $messages);
	
	foreach ($messages as $message) {
		//$res_str .= "AddMessage2Page('". $message['user_nickname'] ."', '". addcslashes($message['message'], "\0..\37!@\177..\377") ."')\n";
		
		//addcslashes($message['message'], "\0..\37!@\177..\377")
		//$dump_cnt = strtr($dump_cnt, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
		$message['message'] = strtr($message['message'], array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
		
		$d = date(LC_DATE_FORMAT, strtotime($message['rec_time']));
		$res_str .= "AddMessage2Page('". $message['user_nickname'] ."', '". $message['message'] ."', '". $d ."', '". $message['service_command'] ."')\n";
		
		// $objResponse->addAlert(LCM_OPPONENT_LOGGED_IN .' - '. $message['service_command']);
		
		switch ($message['service_command']) {
			// have opponent just loged in
			case LCM_OPPONENT_LOGGED_IN:
				//$objResponse->addAlert(LCM_OPPONENT_LOGGED_IN .' - '. $message['service_command']);
			
				// $objResponse->addAlert('asdf');
				// post message
				//$res_str .= "${'opponent_nickname'}.innerHTML = 'opponent name'";
				
				// set opponent session data and JS-data too
				$session_data = LiveChat::GetSessionData($sid);
				if (!empty($session_data['opponent_id'])) {
					// $objResponse->addAlert($session_data['opponent_id']);
					$_SESSION['livechat']['opponent_data'] = LiveChat::GetUserData($session_data['opponent_id']);
					
					$objResponse->addAssign('opponent_nickname', 'innerHTML', $_SESSION['livechat']['opponent_data']['user_nickname']);
					$res_str .= "opponent.user_id       = '{$_SESSION['livechat']['opponent_data']['user_id']}'; \n";
					$res_str .= "opponent.user_nickname = '{$_SESSION['livechat']['opponent_data']['user_nickname']}'; \n";
				}
				else {
					$objResponse->addAlert('server error #59: opponent_id is null');
				}
			
				break;
				
			case LCM_OPPONENT_PING_TIMEOUT:
				if (empty($_SESSION['livechat']['opponent_data']['user_id'])) {
					$objResponse->addAlert('server error #100: opponent_id is null');
				}
				LiveChat::CloseUserSession($_SESSION['livechat']['opponent_data']['user_id']);
				unset($_SESSION['livechat']['opponent_data']);
				$_SESSION['livechat']['close_reason'] = LCM_OPPONENT_PING_TIMEOUT;
				break;
				
			case LCM_SESSION_CLOSED:
				LiveChat::CloseSession($_SESSION['livechat']['sid']);
				unset($_SESSION['livechat']);
				break;
		}
			
		$last_rec_time = $message['rec_time'];
	}
	
	$sd = LiveChat::GetSessionData($_SESSION['livechat']['sid']);
	$opp_id = ($sd['user_id'] == $_SESSION['livechat']['user_data']['user_id']) ? $sd['opponent_id'] : $sd['user_id'];
	$opponent_data = LiveChat::GetUserData($opp_id);

	$objResponse->addScript("oponent_activity = ". (is_numeric($opponent_data['write_activity']) ?  $opponent_data['write_activity'] : 'null') .";");
	$objResponse->addScript("UpdateStatus();");
		
	
	$objResponse->addScript($res_str);
	if ($last_rec_time === null) $last_rec_time = date(LC_DATE_FORMAT);//date('Y-m-d H:i:s'); //2006-05-05 14:52:21
	$objResponse->addScript("user.messages_from = '$last_rec_time'");
	//d LiveChat::RenewUser($_SESSION['livechat']['user_data']['user_id'], array('write_activity' => $write_activity));
	//$objResponse->addAlert($write_activity);
	//$objResponse->addAlert(count($messages) . " - $messages_from - $last_rec_time");
	return $objResponse;
}

function AddFilterForm($index)
{
	// get smarty object
	$_smarty =  new SmartyRendererStandAlone(BASE_DIR.'/modules/Manage/templates', null, true);
	$smarty  =& $_smarty->_smarty;

	// alliases
	$db    =& sxDb::instance();
	$dbIni =  new sxDbIni($db);
	
	$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
	$smarty->assign('sys_options', $sys_options);
	
	$products = new Products($db);
	$smarty->assign('products', $products->GetDataList());
	
	// make assign to users-array
	$users = new Users();
	$at_users = $users->GetDataList();
	__('');
	$at_users = array (
			array (
				'user_id'   => 0,
				'user_name' => __('(unassigned)')
			),
		) + $at_users;
	$smarty->assign('at_users', $at_users);
	
	$smarty->assign('index', $index);
	
	// COPY TO: OHD\modules\Manage\views\QFiltersEditView_success.class.php
	
	$cnt = $smarty->fetch('qFiltersEdit-filterForm.html');
	$cnt = addcslashes($cnt, "\0..\37!@\177..\377");

	$objResponse = new xajaxResponse();
	$objResponse->addScript("
		var tbl = document.getElementById('filters_table');
		var td = tbl.insertRow(tbl.rows.length).insertCell(0);
		td.innerHTML = '$cnt';
	");
	return $objResponse;
}

function ClearFilters() {
	$db    =& sxDb::instance();
	$dbIni =  new sxDbIni($db);
	$dbIni->RemoveGroup(DB_PREF.'sys_options', 'quick_filters');
	
	$objResponse = new xajaxResponse();
	$objResponse->addScript('SaveFilters();');
	return $objResponse;
}

function SaveFilter($id, $props, $criteria = array()) {
	// clear empty criteria
	foreach ($criteria as $k=>$v) {
		if (empty($criteria[$k])) unset($criteria[$k]);
	}
	
	
	// make filter array
	$filter = array (
		$props['name'] => array(
			'props'    => $props,
			'criteria' => $criteria
		)
	);
	
	$db    =& sxDb::instance();
	$dbIni =  new sxDbIni($db);
	$dbIni->SaveIni(DB_PREF.'sys_options', array('quick_filters' => $filter));
	
	$objResponse = new xajaxResponse();
	//$objResponse->addAlert($criteria['priority']);
	return $objResponse;
}

function MakeUserTransfer($curr_user_id, $new_user_id) {
	$objResponse = new xajaxResponse();
	
	if ($_SESSION['livechat']['user_data']['user_id'] != $curr_user_id) {
		$objResponse->addAlert('Error: invalid user id.');
		return $objResponse;
	}
	
	// @TODO: проверка на то что запрашиваеммый тех свободен
	$ud = LiveChat::GetTechRequestStatus($new_user_id);
	if ($ud['lc_user_request']) {
		// TODO: проверка всех состояний
		$objResponse->addAlert('Requested user is chating now...');
	}
	else {
		if (LiveChat::CloseUserSession($_SESSION['livechat']['user_data']['user_id']))
		{
			LiveChat::AddMessage($_SESSION['livechat']['sid'], null, "{$_SESSION['livechat']['user_data']['user_nickname']} logged out.", null, LCM_OPPONENT_LOGGED_OUT);
		    
			$new_user_data = LiveChat::GetUserData($new_user_id);
			LiveChat::AddMessage($_SESSION['livechat']['sid'], null, "Transfering to {$new_user_data['user_nickname']}, please wait..");
			
			LiveChat::RequestTech($new_user_id, $_SESSION['livechat']['opponent_data']['user_id']);
		    
		    // unset($_SESSION['livechat']);
		}
	}
		
	return $objResponse;
}

include '../livechat.common.php';
$xajax->processRequests();

?>