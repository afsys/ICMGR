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

require_once 'Classes/groups.class.php';

class GroupsAddView extends View
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
		$db =& sxDb::instance();

		// get group info on edit group
		$group_id = $request->getParameter('group_id');
		if (!empty($group_id))
		{
			$groups = new Groups($db);
			$group  = $groups->GetGroupData($group_id);

			$renderer->setAttribute('group', $group);
			$renderer->setAttribute('group_id', $group_id);
		}
			
		$renderer->setAttribute('pageBody', 'groupsAdd.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}

}
?>