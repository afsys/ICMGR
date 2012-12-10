<?php

/**
 * LiveChat user.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */

class LcUser
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
	var $user_id = null;
	
	/**
	 * DB User data
	 * @var array
	 */
	var $data = null;
	
	/**
	 * Constructor
	 */
	function LcUser($user_id)
	{
		$this->db      =& sxDB::instance();
		$this->user_id =  $user_id;
	}
	
	/**
	 * Returns user data.
	 * @return    array     user data associative array
	 */
	function GetData()
	{
		$r = $this->db->q("
			SELECT 
			   user_id,
			   sid,
			   agent_id,
			   user_ip,
			   nickname, 
			   actual_time,
			   user_key,
			   created_at,
			   write_activity,
			   IF (actual_time + INTERVAL ". LC_ALLOWED_IDLE_TIME ." > NOW(), status, 'closed_timeout') AS status,
			   status AS db_status,
			   data
			FROM #_PREF_lc_users
			", 
			array ('user_id' => $this->user_id));
		$data = $this->db->fetchAssoc($r);
		
		$data['data'] = unserialize($data['data']);
		if (!$data['data']) $data['data'] = array();
		$this->data = $data;
		
		return $data;
	}
	
	/**
	 * Returns user`s session id number
	 * @return    numeric
	 */
	function GetSid()
	{
		if ($this->data === null) $this->getData();
		return $this->data['sid'];
	}
	
	/**
	 * Mark defined message as readed, so it will be ingnored in LcSession::GetMessagesForUser result
	 * @param     numeric      $message_id     message id number
	 * @param     boolean                      true on success, false in other case
	 */
	function MarkReadedMessage($message_id) 
	{
		$mark_data = array (
			'user_id'    => $this->user_id,
			'message_id' => $message_id,
			'get_time'   => 'NOW()'
		);
		
		$this->db->exception_on_error = false;
		//$this->db->skip_queries_execution = true;
		$this->db->qI('#_PREF_lc_users_messages_status', $mark_data);
		$this->db->skip_queries_execution = false;
		$this->db->exception_on_error = true;
		return true;
	}
	
	/**
	 * Update user`s data and refresh actual_time value.
	 * @param     numeric      $chat_user_id     user id to update
	 * @param     array        $user_data        custom data to update
	 */
	function RefreshUser($user_data = array()) 
	{
		$user_data['actual_time'] = 'NOW()';
		$this->db->qI("#_PREF_lc_users", $user_data, 'UPDATE', array('user_id' => $this->user_id));
	}
	
	/**
	 * Set data
	 */
	function SetData($data)
	{
		$data['data'] = serialize($data['data']);
		$r = $this->db->qI("#_PREF_lc_users", $data, 'UPDATE', array ('user_id' => $this->user_id));
		return true;
	}	
	
	
	/**
	 * Set status
	 * @param     string     $status        new status value
	 * @return    boolean                   true - if new status have been set, false in other case
	 */
	function SetStatus($status)
	{
		$this->db->qI_("#_PREF_lc_users", array ('`status`' => $status, 'actual_time' => 'NOW()'), 'UPDATE', array ('user_id' => $this->user_id));
		return true;
	}
	
}
	
?>