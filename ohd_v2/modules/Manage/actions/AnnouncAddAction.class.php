<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to add announcement.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */
	
class AnnouncAddAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		
		// operate form
		$edit_announc = $request->getParameter('edit_announc');
		if ($edit_announc)
		{
			$ann_data = $request->getParameter('ann');
			$ann_data['ann_date'] = 'NOW()';
			if (empty($ann_data['ann_id'])) {
				$db->qI('#_PREF_announcements', $ann_data);
			}
			else {
				$db->qI('#_PREF_announcements', $ann_data, 'UPDATE', array('ann_id' => $ann_data['ann_id']));
			}
			
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
		// don't handle errors, just redirect to error 404 action
		$controller->forward(ERROR_404_MODULE, ERROR_404_ACTION);
		return VIEW_NONE;
	}

	function registerValidators (&$validatorManager, &$controller, &$request, &$user)
	{

	}
	
	function getPrivilege()
	{
		return NULL;
	}
	
	function isSecure()
	{
		return TRUE;    
	}
}

?>