<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to add user.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

require_once 'Classes/tickets.class.php';
require_once 'Classes/users.class.php';
require_once 'Classes/groups.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/sx_db_ini.class.php';

class RegisterView extends View
{
	
	function &execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer   =& $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		
		
		// error messages
		$message = $request->getParameter('message');
		$message = is_array($message) ? $message : array();
		$renderer->setAttribute('message', $message);
		
		// user data
		$user_data = $request->getParameter('user_data');
		$renderer->setAttribute('user_data', $user_data);
		
		// show thanks page?
		$thanks = $request->getParameter('thanks');

		$renderer->setAttribute('hide_top_panel', 1);
		
		// fetch templates
		if (!$thanks) {
			$renderer->setAttribute('pageBody', 'register.html');
		}
		else {
			$renderer->setAttribute('pageBody', 'register_thanks.html');
		}
		$renderer->setTemplate('../../user_index.html');
		
		return $renderer;
	}
}
?>