<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Ajax router for LC window message.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Aug 31, 2006
 * @version    1.00 Beta
 */
 
require_once "Classes/livechat.class.php";

class ChatWindowAjaxAction extends AjaxAction
{
	function execute (&$controller, &$request, &$user)
	{
		parent::execute($controller, $request, $user);
		
		
		return VIEW_NONE;
	}
	
	
	function getTechs()
	{
		$techs = LiveChat::GetAgentsList('available');
		$this->res['available_techs'] = $techs;
		$this->writeRes();
	}
	
	function sendMessage($params)
	{
		// init
		$lc_session        = new LcSession($params->sid);
		$lc_user           = new LcUser($params->user_id);
		$this->res['date'] = date(LC_DATE_FORMAT);
		$message_user_id   = $params->user_id;
		
		// perform system command operations for user
		switch ($params->service_command) {
			case LCM_USER_LOGGED_OUT:
				$lc_user->setStatus('closed');
				$_SESSION['livechat']['user_data']['status'] = 'closed';
				break;
				
			case LCM_USER_TRANSFER_REQ:
				// close all users with 'requesting_for_transfer' status
				$active_users = $lc_session->GetUsers('active', true);
				foreach ($active_users as $user) {
					if ($user->data['status'] == 'requesting_for_transfer') {
						echo $user->data['nickname'];
					}
				}
				
				
				
				// make message as system message (to show for all users in session)
				$message_user_id = 0; 
				// make agent request
				$lc_session->AddUser(null, 
					                 array('requested_by_user_id' => $params->user_id, 'requested_by_nickname' => $params->nickname), 
				                     $params->message_params->req_user_id, 'requesting_for_transfer');
				break;
		}

		// put message in db
		$res = $lc_session->PutMessage($message_user_id, $params->message, $params->message_params, 
		                               $params->service_command ? $params->service_command : null);
		if (!$res) $this->setResError('Failed to post message!');
		$this->writeRes();
	}
	
	function getUserMessages($params)
	{
		// init
		$lc_session = new LcSession($params->sid);
		$lc_user    = new LcUser($params->user_id);

		// GET MESSAGES
		$messages = array();
		
		$r = $this->db->q('
			SELECT 
			   lc_users.nickname, 
			   messages.*,
			   msg_status.get_time
			FROM #_PREF_lc_messages messages
			LEFT JOIN #_PREF_lc_users lc_users ON messages.user_id = lc_users.user_id
			LEFT JOIN #_PREF_lc_users_messages_status msg_status ON msg_status.message_id = messages.message_id 
			     AND msg_status.user_id = '. $params->user_id .'
			',
			array (
				'messages.sid'        => $params->sid, // сообщения текущей сессии
				'msg_status.get_time' => null, // если сообщение еще не было прочитано данным пользователем
				'AND' => array (
					'#DELIM' => 'OR',
					'messages.user_id'         => array('!=',  $params->user_id), // не выводить свои же сообщения себе
					'messages.service_command' => array('!=',  null), // системные сообщения вывовить всем независимо кто их написал
				)
			)
		);
		
		while ($item = $this->db->fetchAssoc($r)) {
			$item['rec_time'] = date(LC_DATE_FORMAT, strtotime($item['rec_time']));
			$item['message_params'] = unserialize($item['message_params']);
			$messages[] = $item;
			
			$lc_user->MarkReadedMessage($item['message_id']);
		}
		
		// UPDATE USER ACTIVITY
		$write_activity = (is_numeric($params->write_activity) && $params->write_activity > 0) ? $params->write_activity : null;
		$lc_user->RefreshUser(array('write_activity' => $write_activity));
		
		// GET OPPONENTS ACTIVITY
		$opponents = array();
		$r = $this->db->q('
			SELECT 
			   lc_users.user_id,
			   lc_users.nickname,
			   lc_users.write_activity
			FROM #_PREF_lc_users lc_users 
			',
			array (
				'lc_users.sid'            => $params->sid,
				'lc_users.user_id'        => array('!=',  $params->user_id),
				'lc_users.write_activity' => array('<',   10000)
			)
		);
		
		while ($item = $this->db->fetchAssoc($r)) {
			$opponents[$item['user_id']] = $item;
		}
		
		// PERFORM SYSTEM COMMAND OPERATIONS FOR OPPONENTS
		foreach ($messages as $message) {
			switch ($message['service_command']) {
				case LCM_USER_LOGGED_OUT:
					
					// curren user should logout (probably transfering completed)
					if ($item['message_params']['user_id'] == $params->user_id) {
						echo "aaaaaaa";
						$lc_user->setStatus('closed');
						$_SESSION['livechat']['user_data']['status'] = 'closed';
					}
					
					// TODO: @CODE #111 only here to avoid query execution every time on get messages
					break;
			}
		}
		
		// @TODO: заглушка
		// check active users count in session... if only 1 or less - close session
		$active_users = $lc_session->getUsers('active');
		if (count($active_users) <= 1) {
			$reason = 'only one or less users (chatwindow:165)';
			$lc_session->PutMessage(0, "Session closed /$reason/", null, LCM_SESSION_CLOSED);
			$lc_session->Close($reason);
		}

		if (count($opponents)) $this->res['opponents'] = $opponents;
		$this->res['messages'] = $messages;
		$this->writeRes();
	}


	function isSecure()
	{
		return false;   
	}

}

?>