<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to add user
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */
	
class GroupsAddAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();

		// edit group
		if ($request->getParameter('edit_group')) {
			// get data
			$group_caption = $request->getParameter('groupName');
			$group_comment = $request->getParameter('comment');

			// check rights
			$user_rights = $user->getAttribute('user_rights');
			$group_id = $request->getParameter('group_id');
			
			// get group_id
			if (!empty($group_id)) 
			{
				$new_group_id = $group_id;
				$op_name = "REPLACE";
			}
			else
			{
				$new_group_id = $db->getNextId('#_PREF_groups', 'group_id');
				$op_name = "INSERT INTO";
			}
			
			$group_data = array (
				'group_id'        => $new_group_id,
				'group_caption'   => $group_caption,
				'group_comment'   => addslashes(htmlspecialchars(trim($group_comment)))
			);

			$db->qI('#_PREF_groups', $group_data, $op_name);
			
			header('Location: index.php?module=Manage&action=GroupsList');
			exit();
		}
		
		return VIEW_SUCCESS;  
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;
	}

	function handleError (&$controller, &$request, &$user)
	{
		$controller->forward(ERROR_404_MODULE, ERROR_404_ACTION);
		return VIEW_NONE;
	}

	function registerValidators (&$validatorManager, &$controller, &$request, &$user)
	{

	}
	
	function getPrivilege()
	{
		return null;
	}
	
	function isSecure()
	{
		return true;    
	}
}

?>