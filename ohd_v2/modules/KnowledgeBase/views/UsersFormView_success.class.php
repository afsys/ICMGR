<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to manage canned emails templateS.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */
		
class UsersFormView extends View
{
	
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = &$request->getAttribute('SmartyRenderer');

		$renderer->setAttribute('pageBody', 'usersForm.html');
		$renderer->setTemplate('../../index.html');

		return $renderer;
	}
}
?>