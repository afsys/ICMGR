<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Show list of users query.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php'; 

class UsersQueryView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer =& $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		
		$active_users = LiveChat::GetActiveUsersList('users');
		$renderer->setAttribute('active_users', $active_users);
		
		//$renderer->setAttribute('pageBody', 'announcAdd.html');
		$renderer->setTemplate('user_query.html');
		
		return $renderer;
	}

}
?>