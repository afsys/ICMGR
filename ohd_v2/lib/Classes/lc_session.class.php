<?php
	
/**
 * LiveChat session.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'lc_user.class.php';

// LIVE CHAT SERVICE MESSAGES
define('LCM_USER_LOGGED_IN',        'user_logged_in');
define('LCM_USER_LOGGED_OUT',       'user_logged_out');
define('LCM_SESSION_CLOSED',        'session_closed');
define('LCM_USER_TRANSFER_REQ',     'user_transfer_req');

/*
define('LCM_INFO_MESSAGE',          'info_message');
define('LCM_OPPONENT_PING_TIMEOUT', 'ping_timeout');
define('LCM_OPPONENT_PING_TIMEIN',  'ping_timein'); /**/

class LcSession
{
	/**
	 * Database object
	 * @var sxDB
	 */
	var $db = null;

	/**
	 * Session id-number
	 * @var numeric
	 */
	var $sid = null;
	
	/**
	 * Session data from database
	 * @var array
	 */
	var $data = null;
	
	/**
	 * Constructor
	 */
	function LcSession($sid)
	{
		$this->db  =& sxDB::instance();
		$this->sid =  $sid;
	}
	
	/**
	 * Adds new user to chat session
	 * @param     string     $user_key       user's key for validating user
	 * @param     string     $message        message for adding
	 * @return    LcUser                     LcUser object referene
	 */	
	function AddUser($nickname, $user_data = array(), $agent_id = null, $initial_status = 'created')
	{
		if (!$user_data) $user_data = array();
		
		// get nickname if aget_id given
		if ($agent_id !== null) {
			// if user for current agent already exists - just update status
			$r = $this->db->q('SELECT * FROM #_PREF_lc_users', array('agent_id' => $agent_id, 'sid' => $this->sid));
			$agent_data = $this->db->fetchAssoc($r);
			if (!empty($agent_data['agent_id']) && $agent_data['agent_id'] == $agent_id) {
				$lc_user = new LcUser($agent_data['user_id']);
				$lc_user->setStatus($initial_status);
				if (!empty($agent_data)) $lc_user->setData(array('data' => $user_data));
				return $lc_user;
			}
			
			$nickname = $this->db->getOne('SELECT user_name FROM #_PREF_users', array('user_id' => $agent_id));
		}
		
		
		$user_ip = get_user_ip();
		$user_hostname = gethostbyaddr($user_ip);
		
		$this->db->qI(
			'#_PREF_lc_users', 
			array(
				'sid'              => $this->sid,
				'agent_id'         => $agent_id,
				'nickname'         => $nickname,
				'created_at'       => 'NOW()', 
				'status'           => $initial_status,
				'user_key'         => md5(time()),
				'user_ip'          => $user_ip,
				'hostname'         => $user_hostname,
				'data'             => serialize($user_data),
			    'actual_time'      => 'NOW()', 
			)
		);
		
		$user_id = $this->db->lastInsertId();
		return new LcUser($user_id);
	}
	
	/**
	 * Closes session and all session users.
	 */
	function Close($other_close_reason = null)
	{
		$this->db->qI(
			'#_PREF_lc_sessions', 
			array('closed' => 1, 'other_close_reason' => $other_close_reason, 'closed_at' => 'NOW()'), 
			'UPDATE', array('sid' => $this->sid)
		);
		$this->db->qI('#_PREF_lc_users', array('`status`' => 'closed'), 'UPDATE', array('sid' => $this->sid));
	}
	
	
	/**
	 * Returns session data.
	 * @return    array     session data associative array
	 */
	function GetData()
	{
		$this->db->q("SELECT * FROM #_PREF_lc_sessions", array ('sid' => $this->sid));
		$this->data = $data = $this->db->fetchAssoc();
		
		$data['data'] = unserialize($data['data']);
		if (!$data['data']) $data['data'] = array();
		
		return $data;
	}
	
	/**
	 * Returns array message per chat
	 * @param     numeric    $sid     chat session id
	 * @return    array      array of messages
	 */
	function GetMessages()
	{
		$trns = LiveChat::GetSessionsHeaders(array('sid' => $this->sid));
		$trns = $trns[0];
		
		$r = $this->db->q("
			SELECT *
			FROM #_PREF_lc_messages messages
			   LEFT JOIN #_PREF_lc_users users ON messages.user_id = users.user_id
			WHERE messages.sid = ". $this->sid ."
		");
		
		$chat = array();
		while ($item = $this->db->fetchAssoc()) {
			$chat[] = $item;
		}
		
		$trns['messages'] = $chat;
		return $trns;
	}
	
	/**
	 * Returns set of opponent messages from defined date and for current chat session.
	 * @param     numeric      $curr_user_id     current user id
	 * @param     numeric      $sid              current session id
	 * @param     string       messages_from     start date for getting messages or NULL for all messages
	 * @param     array                          array of messages
	 */
	function GetMessagesForUser($curr_user_id, $sid, $messages_from = null)
	{
		$where_clause = array (
			'#WHERE' => array (
				'messages.sid' => $sid, 
				'messages.user_id'  => array('!=', $curr_user_id),
			),
			'#ORDER' => 'messages.rec_time'
		);
		
		$where_clause['#WHERE']['get_status.user_id'] = null;
		
		/* old style by time if ($messages_from !== null) {
			$where_clause['#WHERE']['messages.rec_time'] = array('>', $messages_from);
		} */
		
		$this->db->q("
			SELECT 
			   users.user_nickname,
			   messages.conversation_id,
			   messages.message,
			   messages.message_params,
			   messages.rec_time,
			   messages.service_command
			FROM #_PREF_lc_messages messages
			   INNER JOIN #_PREF_lc_users users ON messages.user_id = users.user_id
			   LEFT JOIN #_PREF_lc_users_messages_status get_status ON get_status.message_id = messages.message_id
			", $where_clause
		);

		$messages = array();
		while ($item = $this->db->fetchAssoc()) {
			$item['message_params'] = unserialize($item['message_params']);
			$messages[] = $item;
		}
		
		return $messages;
	}
	
	/**
	 * Returns array of users for current session
	 * @param     string         $set_type     define set of result type: enum (all, active)
	 * @return    LcUsers array                LcUser object referenes array
	 */
	function GetUsers($set_type = 'all', $get_data = false)
	{
		$where_clause = array('sid' => $this->sid);
		
		switch ($set_type) {
			case 'active':
				$where_clause['status'] = array ('!=', 'closed');
				$where_clause['actual_time + INTERVAL '. LC_ALLOWED_IDLE_TIME] = array ('>', 'NOW()');
				break;
				
			case 'all':
				break;
			
			case 'requester':
				$where_clause = array (
					'#WHERE' => array (
						'sid' => $this->sid
					),
					'#ORDER' => 'user_id ASC',
					'#LIMIT' => 1
				);
				break;
				
			default:
				if (is_array($set_type)) {
					$where_clause = $set_type;
				}
				break;
		}
		
		$r = $this->db->q('SELECT user_id FROM #_PREF_lc_users', $where_clause);
		$users = array();
		while ($ud = $this->db->fetchAssoc($r)) {
			$user = new LcUser($ud['user_id']);
			if ($get_data) $user->getData();
			$users[] = $user;
		}
		
		return $users;
	}
	
	/**
	 * Returns true if session closed
	 * @return    boolean
	 */
	function IsClosed()
	{
		if ($this->data === null) $this->GetData();
		return $this->data['closed'] == 1;
	}
	
	
	/**
	 * Put message message into chat session
	 * @param     numeric    $user_id        user`s id number or 0 for system message
	 * @param     string     $message        message for adding
	 * @return    boolean                    true on success, else - false
	 */
	function PutMessage($user_id, $message, $message_params = null, $service_command = null) 
	{
		/*// add direct message
		if ($user_key === null) {
			$sid     = $item_id;
			$user_id = 0;
		}
		// user messge post
		else {
			$sid = $this->db->getOne("SELECT sid FROM #_PREF_lc_users", array('user_id' => $item_id, 'user_key' => $user_key));
		}*/
		
		$this->db->qI("#_PREF_lc_messages", 
			array (
				'user_id'         => $user_id,
				'sid'             => $this->sid,
				'message'         => $message,
				'message_params'  => serialize($message_params),
				'service_command' => $service_command,
				'rec_time'        => 'NOW()'
			)
		);
		return true;
	}
}


?>