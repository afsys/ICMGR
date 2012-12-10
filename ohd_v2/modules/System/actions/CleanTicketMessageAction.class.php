<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Action for ...
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    June 6, 2006
 * @version    1.00 Beta
 */


class CleanTicketMessageAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{  
		return VIEW_SUCCESS;
	}

	function isSecure ()
	{
		return true;
	}
}

?>