<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action for retrieving agent statuses insformation in JSON format.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php';
require_once 'PEAR/JSON.php';

class GetAgentStatusesAjaxAction extends AjaxAction
{
	
	function execute (&$controller, &$request, &$user)
	{
		parent::execute($controller, $request, $user);
		
		
		return VIEW_NONE;
	}
	
	
	function getAgentStatuses()
	{
		$agent_id = $params->agent_id ? $params->agent_id : $this->user->getAttribute('user_id');
		$statuses = LiveChat::GetAgentStatuses($agent_id);
	
		foreach ($statuses as $k=>$status) {
			// обновлять только пользователей со статусом 'request*'
			// так как иначе пользователь будет активным во время разговора даже если окно чата будет закрыто
			if ($status['status'] != 'requesting' && $status['status'] != 'requesting_for_transfer') continue;
			
			// @CODE #111
			// check active users count in session... if only 1 or less - close session
			$lc_session = new LcSession($status['sid']);
			$active_users = $lc_session->getUsers('active');
			if (count($active_users) <= 1) {
				$reason = 'only one or less users (agentstatus:49)';
				$lc_session->PutMessage(0, "Session closed /$reason/", null, LCM_SESSION_CLOSED);
				$lc_session->Close($reason);
				
				unset($statuses[$k]);
				continue;
			}

			$lc_user = new LcUser($status['user_id']);
			$lc_user->RefreshUser();
		}
		
		
		//var_dump($statuses);
		$this->res['agent_statuses'] = $statuses;
		$this->writeRes();
		//header('X-JSON: {"errorCode":0,"errorMessage":"Ok!","agent_statuses":[{"sid":"2","status":"chating"},{"sid":"1","status":"requesting"}]}');
		//header('X-JSON: {"errorCode":0,"errorMessage":"Ok!","agent_statuses":[{"sid":"1","status":"chating"}]}');
		//header('X-JSON: {"errorCode":0,"errorMessage":"Ok!","agent_statuses":[]}');
	}	
	
/*	function execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$db       =& sxDb::instance();
		$renderer =& $request->getAttribute('SmartyRenderer');
		$user_id  =  $user->getAttribute('user_id');
		$json     =  new Services_JSON();
		
		header("Content-type: text/javascript");
		//header("X-JSON: [1, 2]");
		$user_data = LiveChat::GetTechRequestStatus($user_id);
		
		// determine tech status
		$db->q("
			SELECT 
			   users.sid,
			   users.actual_time,
			   users.actual_time + INTERVAL ". LC_ALLOWED_IDLE_TIME ." > NOW() AS allowed_actual_time
			FROM #_PREF_lc_users users
			   LEFT JOIN #_PREF_lc_sessions sessions ON users.sid = sessions.sid
			   LEFT JOIN #_PREF_users agents ON users.ohd_user_id = agents.user_id
			", 
			
			array(
				'users.ohd_user_id' => $user_id, 
				'users.closed' => 0,
				'users.actual_time + INTERVAL '. LC_ALLOWED_IDLE_TIME => array('>', 'NOW()')
			)
		);
		$ud = $db->fetchAssoc();
		//dump($ud);
		
		if ($ud['sid'] && $ud['allowed_actual_time']) $curr_status = 'chating';
		else if ($user_data['lc_user_request'] && $user_data['allowed_actual_time']) {
			if ($user_data['is_agent_request']) $curr_status = 'agent_transfer';
			else $curr_status = 'requesting';
		}
		else $curr_status = 'none';
		
		$lc_curr_user_data = array (
			user_id               =>  $user_id, 
			lc_user_request       =>  $user_data['lc_user_request'] ? $user_data['lc_user_request'] : null,
			lc_user_request_time  =>  $user_data['lc_user_request_time'] ? $user_data['lc_user_request_time'] : null,
			curr_status           =>  $curr_status
		);
		
		echo "lc_curr_user_data = ".$json->encode($lc_curr_user_data);
		return VIEW_NONE;  
	} */
	

	
	function isSecure()
	{
		return false;
	}
}

?>