<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show piping accounts list
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Feb 15, 2006
 * @version    1.00 Beta
 */

class PipingAccListAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$db =& sxDb::instance();

		// delete
		$delete = $request->getParameter('delete');
		$acc_id = $request->getParameter('acc_id');
		if ($delete && $acc_id)
		{
			$db->qD('#_PREF_piping_accounts', array('acc_id' => $acc_id));
			
			header('Location: index.php?module=Manage&action=PipingAccList');
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