<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show users notes list for approvement.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Feb 7, 2006
 * @version    1.00 Beta
 */
	
class SearchAction extends Action
{
	function execute(&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;  
	}
	
	function getDefaultView(&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;
	}

	function handleError(&$controller, &$request, &$user)
	{
		$controller->forward(ERROR_404_MODULE, ERROR_404_ACTION);
		return VIEW_NONE;
	}

	function registerValidators(&$validatorManager, &$controller, &$request, &$user)
	{

	}
	
	function getPrivilege()
	{
		return null;
	}
	
	function isSecure()
	{
		return false;    
	}
}

?>