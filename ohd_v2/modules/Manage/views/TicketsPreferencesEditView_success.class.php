<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to manage system preferences.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

require_once 'Classes/sx_db_ini.class.php';

class TicketsPreferencesEditView extends View
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = & $request->getAttribute('SmartyRenderer');
		$sys_options = $user->getAttribute('sys_options');
		
		$renderer->setAttribute('sys_options', $sys_options);
		
		$renderer->setAttribute('pageBody', 'ticketsPreferencesEdit.html');
		$renderer->setTemplate('../../index.html');
		return $renderer;
	}

	/**
	* There's no cleanup to do for this view.
	*
	function cleanup ()
	{

	} */
}
?>