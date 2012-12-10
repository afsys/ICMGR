<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * List Email Piping Filters.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Mar 22, 2006
 * @version    1.00 Beta
 */
 
class ListFiltersAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
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

	function getPrivilege()
	{
	      //all authenticated users have access to this module
	     return null;
	}

	function isSecure()
	{
	     return true;
	}
}
?>