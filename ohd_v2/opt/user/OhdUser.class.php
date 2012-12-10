<?php

require_once 'PrivilegeUser.class.php';
require_once 'Classes/livechat.class.php';

class OhdUser extends PrivilegeUser
{
	function & OhdUser ()
	{
		parent::PrivilegeUser();
	}
    
	function GetTicketsRightsLimitClause($external_where = array())
	{
		$user_data = $this->getAttribute('user_data');
		if (!$user_data['is_sys_admin']) {
			// init items
			$rights_where = array ('#ARRAYS' => array());
			$user_rights  = $this->getAttribute('user_rights');
			
			
			// CREATE OR-relation RIGHTS
			// add creator and assigned to values
			$OR_where = array (
				'#DELIM' => 'OR',
				'tickets.creator_user_id' => $user_data['user_id'],
				'tickets.assigned_to'     => $user_data['user_id']
			);
			
			// SR_TL_VIEW_OTHERS rights
			if (($user_rights & SR_TL_VIEW_OTHERS) == SR_TL_VIEW_OTHERS) {
				$OR_where = array (
					'tickets.assigned_to' => array ('!=', 0),
					'OR' => $OR_where
				);
			}
			
			// SR_TL_VIEW_UNASSIGNED rights
			if (($user_rights & SR_TL_VIEW_UNASSIGNED) == SR_TL_VIEW_UNASSIGNED) {
				$OR_where = array (
					'tickets.assigned_to' => 0,
					'OR' => $OR_where
				);
			}
			
			// apply OR-rights
			$rights_where['#ARRAYS'][] = $OR_where;
			
			
			
			// OTHER AND-relation RIGHTS
			if ($user_data['is_customer']) {
				
			}
			else {
				// add user group rights to view only
				$db =& sxDb::instance();
				$db->q('SELECT * FROM #_PREF_users_groups', array('user_id' => $user_data['user_id']));
				$groups = array(0);
				while ($itm = $db->fetchAssoc()) {
					$groups[] = $itm['group_id'];
				}
				
				//$rights_where['#ARRAYS'][] = array('tickets.group_id' => $groups);
			}
			
			// combine $rights_where and $external_where
			if (!empty($external_where)) {
				$rights_where['#ARRAYS'][] = $external_where;
			}
			$external_where = $rights_where;
		}
		
		//dump($external_where);
		return $external_where;
/*
		$user_data = $this->getAttribute('user_data');
		
		if (!$user_data['is_sys_admin']) {

		
			// add user groups
			if ($user_data['is_customer']) {
				
			}
			else
			{
				$db =& sxDb::instance();
				$db->q('SELECT * FROM #_PREF_users_groups', array('user_id' => $user_data['user_id']));
				$groups = array(0);
				while ($itm = $db->fetchAssoc()) {
					$groups[] = $itm['group_id'];
				}
				$curr_where = array (
					'tickets.group_id' => $groups,
					'AND' => $ticket_where
				);
				$ticket_where = $curr_where;
			}
			
			// add custom filter
			if (count($filter_where)) {
				if (!empty($filter_where['AND'])) die('');
				$filter_where['AND'] = $ticket_where;
				//dump($filter_where); 
			}
		}
		
		
		return $ticket_where;
*/
	}
    
    
	// USER OPTIONS
	// defaut optoins
	var $def_options = array (
		'defaults' => array (
			'tickets_list_orderby' => 'id',
			'tickets_list_orderto' => 'ASC',
			'tickets_per_page'     => 15,
			'messages_per_page'    => 20,
			'page_autorefresh'     => '0',
			'time_zone'            => 0,
			'time_format'          => '%A %d %b %Y @ %H:%M by %s',
			'time_format_short'    => '%d %b %Y',
			'language'             => 'en-us', //$sys_options['common']['language'],
			'tickets_popups'       => 'none',
			'session_livetime'     => 0,
			'tickets_list_style'   => 'extended',
			'page_autorefresh_check_piping' => 0,
			'enable_livechat'      => 0,
		),
		
		'lc' => array (
			'req_rings_count' => 5,
		),

		'notification_emails' => array (
			'defect_assigment' => 1,
			'defect_changed'   => 1,
			'defect_added'     => 1,
			'defect_closed'    => 1,
		),
		
		'tickets_edit' => array (
			'quick_navigation_type' => 'none', //'prev_next',
			'todo_style'            => 'closed_if_empty',
			'editors_type'          => 'simple'
		),
			
		'user_variables' => array()
	);    
	
	
	function GetOptions($apply_defaults = true, $user_id = null) {
		if ($user_id === null) $user_id = $this->getAttribute('user_id');
		if (empty($user_id) && $this->isAuthenticated()) die('opt\user\OhdUser.class.php : 78 - user_id is null');

		require_once 'Classes/sx_db_ini.class.php';
		$dbIni =  new sxDbIni();
		$user_options = $dbIni->LoadIni(DB_PREF.'users_options', array('user_id' => $user_id));
		if ($apply_defaults) $user_options = $dbIni->imposeArray($this->def_options, $user_options);
		return $user_options;
	}
	
	function GetLiveChatTalkingStatus($user_id = null) {
		return array();
		if (!$this->isAuthenticated()) return null;
		
		if ($user_id === null) $user_id = $this->getAttribute('user_id');
		if (empty($user_id)) die('opt\user\OhdUser.class.php : 90 - user_id is null');
		
		$db =& sxDb::instance();
		
		$cnt = $db->getOne("
			SELECT COUNT(*)
			FROM #_PREF_lc_users users
			   LEFT JOIN #_PREF_lc_sessions sessions ON users.sid = sessions.sid", 
			array(
				'users.ohd_user_id' => $user_id, 
				'users.closed' => 0,
				'actual_time + INTERVAL '. LC_ALLOWED_IDLE_TIME => array('>', 'NOW()')
			)
		);
		if ($cnt > 1) echo "\opt\user\OhdUser.class.php : 113 - #_PREF_lc_users users greater than 1 <br />";
		
		$db->q("
			SELECT 
			   users.sid,
			   users.actual_time,
			   users.actual_time + INTERVAL ". LC_ALLOWED_IDLE_TIME ." > NOW() AS allowed_actual_time
			FROM #_PREF_lc_users users
			   LEFT JOIN #_PREF_lc_sessions sessions ON users.sid = sessions.sid", 
			array(
				'users.ohd_user_id' => $user_id, 
				'users.closed' => 0,
				'actual_time + INTERVAL '. LC_ALLOWED_IDLE_TIME => array('>', 'NOW()')
			)
		);
		$user_data = $db->fetchAssoc();
		
		return $user_data;
	}


	function GetLiveChatReqStatus($user_id = null) {
		return array();
		if (!$this->isAuthenticated()) return null;
		
		if ($user_id === null) $user_id = $this->getAttribute('user_id');
		if (empty($user_id)) die('opt\user\OhdUser.class.php : 100 - user_id is null');
				
		$user_data = LiveChat::GetTechRequestStatus($user_id);
		return $user_data;
	}
	
	function UpdateUserActivity()
	{
		$user_id = $this->getAttribute('user_id');
		
		$db =& sxDb::instance();
		$db->qI('#_PREF_users', array('actual_time' => 'NOW()'), 'UPDATE', array('user_id' => $user_id));
	}
}

?>