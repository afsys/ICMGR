<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show page.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    03 Jan, 2006
 * @version    1.00 Beta
 */

class HaveNoRightsAction extends Action
{
	function execute () { return VIEW_SUCCESS; }
	function getDefaultView () { return VIEW_SUCCESS; }
	function getRequestMethods () { return REQ_NONE; }
	function isSecure () { return false; }
}

?>
