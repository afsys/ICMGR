<?php
	
/**
 * LiveChat manipulation class.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */

require_once 'lc_session.class.php';

class LiveChat
{
	/**
	 * Database object
	 * @var sxDB
	 */
	var $db = null;
	
	/**
	 * Constructor
	 */
	function LiveChat()
	{
		$this->db =& sxDB::instance();
	}
	
	/**
	 * Try join to chat with defined user.
	 * @param     numeric      $group_id            department (group) id-number
	 * @param     LcSession                         currently opened LcSession object reference
	 */
	function CreateSession($group_id, $data = array()) {
		// start new session
		$db =& sxDB::instance();
		$db->qI('#_PREF_lc_sessions', array('group_id' => $group_id, 'created_at' => 'NOW()', 'closed' => 0, 'data' => serialize($data)));
		$sid = $db->lastInsertId();
		
		return new LcSession($sid);
	}
	
	/**
	 * Returns array of active! agent statuses.
	 * @param     numeric    $user_id   lc_user id
	 * @return    array      user request statuses and SID
	 */
	function GetAgentStatuses($agent_id) 
	{
		$db =& sxDB::instance();
		$db->q('
			   SELECT 
			      lc_users.user_id,
			      lc_users.sid,
			      IF (lc_users.actual_time + INTERVAL '. LC_ALLOWED_IDLE_TIME .' > NOW(), lc_users.status, "closed_timeout") AS `status`,
			      lc_users.`status` AS db_status,
			      lc_sessions.data AS sdata
			   FROM #_PREF_users agents
			   INNER JOIN #_PREF_lc_users lc_users ON agents.user_id = lc_users.agent_id
			   INNER JOIN #_PREF_lc_sessions lc_sessions ON lc_users.sid = lc_sessions.sid
			', 
			array(
				'agents.user_id'   => $agent_id,
				'LOCATE("closed", IF(lc_users.actual_time + INTERVAL '. LC_ALLOWED_IDLE_TIME .' > NOW(), lc_users.status, "closed"))'  => array('!=', 1	)
			)
		);
		
		$statuses = array();
		while ($item = $db->fetchAssoc()) {
			$item['sdata'] = unserialize($item['sdata']) ? unserialize($item['sdata']) : array();
			$statuses[] = $item;
		}
		return $statuses;
	}
	
	/**
	 * Returns array of available agents.
	 * @param     string     $set_type   type of result set enum ('all', 'avaiable')
	 * @param     numeric    $group_id   group id for requesting techs
	 * @return    array      array of currently available users.
	 */
	function GetAgentsList($set_type = 'available', $group_id = null) 
	{
		$db =& sxDB::instance();
	
		switch ($set_type) {
			case 'all':
				$where_clause = array('is_customer' => 0);
				break;
				
			case 'avaiable':
			case 'available':
				// @TODO: вставить проверку на то чтобы не вызывать пользователя с которым ты уже разговариваешь.
				
				$where_clause = array (
					'#WHERE' => array (
						'users.is_customer' => 0,
						'users.lc_enabled'  => 1,
					    'users.actual_time + INTERVAL '. LC_ALLOWED_IDLE_TIME => array('>=', 'NOW()')
					)
				);
				
				if ($group_id !== null) {
					$where_clause['#WHERE']['groups.group_id'] = $group_id;
				}
				
				break;
		}
		
		$where_clause['#ORDER'] = 'lc_priority DESC';
		//$where_clause['#LIMIT'] = '5';
		
		$db->q("
			SELECT users.* 
			FROM #_PREF_users users
			   #LEFT JOIN #_PREF_lc_users lc_users ON users.lc_user_request = lc_users.user_id
			   ". ($group_id !== null ? "LEFT JOIN #_PREF_users_groups groups ON users.user_id = groups.user_id" : "") ."
		", $where_clause);
		
		$users = array();
		while ($item = $db->fetchAssoc()) {
			$users[] = $item;
		}
		
		return $users;
	}
	
	
	function GetSessionsHeaders($limit_clause = array())
	{
		$db =& sxDB::instance();
		$r = $db->q("
			SELECT
			   *
			FROM #_PREF_lc_sessions
			",
			$limit_clause
		);
		
		$items = array();
		while ($item = $db->fetchAssoc($r)) {
			$db->q("
				SELECT 
				   UNIX_TIMESTAMP(MAX(rec_time)) - UNIX_TIMESTAMP(MIN(rec_time)) AS duration,
				   SUM(LENGTH(message)) AS size
				FROM #_PREF_lc_messages", 
				array('sid' => $item['sid']));
			$info = $db->fetchAssoc();
			
			$item['duration'] = $info['duration'];
			$item['size']     = $info['size'];
			$item['data']     = unserialize($item['data']) ? unserialize($item['data']) : array();
			
			$items[] = $item;
		}
		
		return $items;
	}

	/* =============== OLD FUNCTIONS ================== */

	/**
	 * Gets user data by user id.
	 * @param     numeric    $user_id   lc_user id
	 * @return    array      user data
	 */
	function GetUserData($user_id)
	{
		$db =& sxDB::instance();
		$db->q("
			SELECT 
			   users.*,
			   IF (actual_time + INTERVAL 1 MINUTE > NOW(), 1, 0) AS is_active
			FROM #_PREF_lc_users users", 
			array ('user_id' => $user_id)
		);
		return $db->fetchAssoc();
	}
	
	/**
	 * Gets user status by user id.
	 * @param     numeric    $user_id   lc_user id
	 * @return    string     status enum (none, active, closed, ping timeout)
	 */	
	function GetUserStatus($user_id)
	{
		if (empty($user_id)) return 'none';
		$ud = LiveChat::GetUserData($user_id);
		if ($ud['is_active']) return 'active';
		else if ($ud['closed']) return 'closed';
		else return 'ping timeout';
	}
	
}

?>