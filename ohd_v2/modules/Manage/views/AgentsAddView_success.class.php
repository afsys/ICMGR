<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to add user.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

require_once 'Classes/users.class.php';
require_once 'Classes/groups.class.php';

class AgentsAddView extends View
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		$renderer =& $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		
		// make groups list
		$groups_list = array();
		$r = $db->q("
		   SELECT 
		      g.group_id,
		      g.group_caption,
		      g.group_comment
		   FROM #_PREF_groups g
		");
		while ($data = $db->fetchAssoc()) $groups_list[] = $data;
		
		// get user data
		$user_id  =  $request->getParameter('user_id');
		if (!empty($user_id))
		{
			$users = new Users($db);
			$renderer->setAttribute('user_id', $user_id);
			$user_data = $users->GetUserData($user_id);
			$renderer->setAttribute('user_data', $user_data);
			
			$user_groups = array();
			foreach ($user_data['groups'] as $gr) $user_groups[] = $gr['group_id'];
			foreach ($groups_list as $k=>$v) {
				if (in_array($groups_list[$k]['group_id'], $user_groups)) $groups_list[$k]['checked'] = true;
			}
		}
		else
			$privileges = array();
		
		// array of rights
		global $SR_NOTES;
		$renderer->setAttribute('SR_NOTES', $SR_NOTES);
		
		
		// lc_weights
		$lc_weights = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
		$renderer->setAttribute('lc_weights', $lc_weights);
		
		
		
		// user_data
		$user_data = $request->getParameter('user_data');
		if (is_array($user_data)) $renderer->setAttribute('user_data', $user_data);
		
		// error messages
		$message = $request->getParameter('message');
		$message = is_array($message) ? $message : array();
		$renderer->setAttribute('message', $message);
		
		//
		$renderer->setAttribute('groups', $groups_list);
		$renderer->setAttribute('pageBody', 'agentsAdd.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}

}
?>