<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to ...
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Oct 10, 2006
 * @version    1.00 Beta
 */

class TicketsStatisticAgentsAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		
		
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
		
	function isSecure()
	{
		return true;    
	}
}

?>