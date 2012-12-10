<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Ajax router for LC transfering
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Aug 31, 2006
 * @version    1.00 Beta
 */

class TransferAjaxAction extends AjaxAction
{
	function execute (&$controller, &$request, &$user)
	{
		parent::execute($controller, $request, $user);
		
		
		return VIEW_NONE;
	}
	
	function makeAgentRequest()
	{
		echo "alert(1);";
	}


}
?>