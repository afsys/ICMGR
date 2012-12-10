<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to show groups list.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

require_once 'Classes/groups.class.php';

class GroupsListView extends View
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = & $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();

		// get user`s groups
		$groups = new Groups($db);
		$renderer->setAttribute('groups', $groups->GetDataList());
		
		// FIND LOST TICKETS
		// get ids array of all groups
		$group_ids = array(0);
		$db->q('SELECT DISTINCT group_id FROM #_PREF_groups');
		while ($i = $db->fetchAssoc()) $group_ids[] = $i['group_id'];
		// get lost tickets
		$db->q('SELECT group_id, COUNT(*) AS cnt FROM #_PREF_tickets', 
		        array(
		            '#WHERE' => array('group_id' => array('NOT IN', $group_ids)),
		            '#GROUP' => 'group_id',
		            '#ORDER' => 'group_id'
		        )
		);
		
		$lost_tickets = array();
		while ($item = $db->fetchAssoc()) $lost_tickets[] = $item;
		$renderer->setAttribute('lost_tickets', $lost_tickets);
		
		//dump($lost_tickets);
		
		// user`s rights
		$user_rights = $user->getAttribute('user_rights');
		$renderer->setAttribute('user_rights', $user_rights);
		
		$renderer->setAttribute('pageBody', 'groupsList.html');
		$renderer->setTemplate('../../index.html');
		return $renderer;
	}
}
?>