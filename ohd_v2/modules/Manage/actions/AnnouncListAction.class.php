<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show announcements list
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

class AnnouncListAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$db =& sxDb::instance();

		// delete
		$delete = $request->getParameter('delete');
		$ann_id = $request->getParameter('ann_id');
		if ($delete && $ann_id)
		{
			$db->qD('#_PREF_announcements', array('ann_id' => $ann_id));
			
			header('Location: index.php?module=Manage&action=AnnouncList');
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