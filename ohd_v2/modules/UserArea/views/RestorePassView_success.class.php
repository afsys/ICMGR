<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to restore user`s pass.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */


class RestorePassView extends View
{
	function &execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer   =& $request->getAttribute('SmartyRenderer');
		
		$renderer->setAttribute('res_message', $request->getParameter('res_message'));
		
		$renderer->setAttribute('hide_top_panel', 1);
		$renderer->setAttribute('pageBody', 'restorePass.html');
		$renderer->setTemplate('../../user_index.html');
		
		return $renderer;
	}
}
?>