<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to ...
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Oct 10, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/users.class.php';
 
class TicketsStatisticAgentsView extends View
{
	function & execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db       =& sxDb::instance();
		$renderer =& $request->getAttribute('SmartyRenderer');
		
		$view_type = $request->getParameter('type');
		if (empty($view_type)) $view_type = 'WeekMonth';
		
		// prepare array
		$users = new Users();
		$agents = array();
		$agents_list = $users->GetDataList(0, 0, array('u.is_customer' => 0));
		foreach ($agents_list as $agent) {
			$agents[$agent['user_name']] = array();
		}
		
		switch ($view_type) {
			case 'LastWeek':
				$days = array();
				// fill array by last week days
				for ($i = 6; $i >= 0; $i--) {
					$days_before = strtotime("-$i day");
					$str = date('Y-m-d', $days_before);
					$days[] = $str;
					
					$renderer->setAttribute('last_week_days',  $days);
				}
				
				// fill by agents
				$db->q("
					SELECT
					   user_name, DATE_FORMAT(message_datetime, '%Y-%m-%d') AS message_datetime, message_type, COUNT(*) AS cnt
					FROM #_PREF_tickets_messages messages
					INNER JOIN #_PREF_users users ON messages.message_creator_user_id = users.user_id
					WHERE message_datetime >= NOW() - INTERVAL 6 DAY
					GROUP BY user_name, message_datetime, message_type
					ORDER BY user_name, message_datetime, message_type
				");
			
				while ($item = $db->fetchAssoc()) {
					if (empty($agents[$item['user_name']])) $agents[$item['user_name']] = array($item['message_datetime'] => array());
					$agents[$item['user_name']][$item['message_datetime']][$item['message_type']] = $item['cnt'];
				}
				break;
			
			case 'WeekMonth':
				$db->q("
					SELECT
					   user_name, message_type, COUNT(*) AS cnt
					FROM #_PREF_tickets_messages messages
					INNER JOIN #_PREF_users users ON messages.message_creator_user_id = users.user_id
					WHERE message_datetime >= NOW() - INTERVAL 6 DAY
					GROUP BY user_name, message_type
					ORDER BY user_name, message_type
				");
				
				while ($item = $db->fetchAssoc()) {
					if (empty($agents[$item['user_name']])) $agents[$item['user_name']] = array('last_week' => array());
					$agents[$item['user_name']]['last_week'][$item['message_type']] = $item['cnt'];
				}
				
				$db->q("
					SELECT
					   user_name, message_type, COUNT(*) AS cnt
					FROM #_PREF_tickets_messages messages
					INNER JOIN #_PREF_users users ON messages.message_creator_user_id = users.user_id
					WHERE message_datetime >= NOW() - INTERVAL 30 DAY
					GROUP BY user_name, message_type
					ORDER BY user_name
				");
				
				while ($item = $db->fetchAssoc()) {
					if (empty($agents[$item['user_name']])) $agents[$item['user_name']] = array('last_month' => array());
					$agents[$item['user_name']]['last_month'][$item['message_type']] = $item['cnt'];
				}
				break;
		}
		
		$renderer->setAttribute('view_type',  $view_type);
		$renderer->setAttribute('agents',  $agents);
		
		$renderer->setAttribute('pageBody', 'ticketsStatisticAgents.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}
}

?>
