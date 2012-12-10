<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Action for showing JS code.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Mar 22, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);    
require_once 'Classes/canned_emails.class.php';    
	
class GetJsAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;  
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;
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