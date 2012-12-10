<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Show list of users query.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php'; 
require_once 'Classes/groups.class.php'; 


class ChatRequestView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer =& $request->getAttribute('SmartyRenderer');
		$db       =& sxDb::instance();
		$groups   =  new Groups();
		
		// get groups and disable if there is no any active agent in group
		$groups_list = $groups->GetDataList();
		foreach ($groups_list as $k=>$v) {
			$groups_list[$k]['active_agents_count'] = count(LiveChat::GetAgentsList('available', $v['group_id']));
		}
		
		$renderer->setAttribute('groups', $groups_list);
		$renderer->setTemplate('chat_request.html');
		
		return $renderer;
	}

}
?>