<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Starts LiveChat user session.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php';
	
class StartAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		
		if (!empty($_SESSION['livechat']['user_data']['user_id'])) {
			LiveChat::RenewUser($_SESSION['livechat']['user_data']['user_id']);
			header('Location: index.php?module=LiveChat&action=UsersQuery');
			exit();
		}
		
		$user_data = array();
		// TODO: set user_data IP
		if ($user->isAuthenticated()) {
			$user_data['ohd_user_id']   = $user->getAttribute('user_id');
			$user_data['user_nickname'] = $user->getAttribute('name');
		}
		
		$user_data = LiveChat::AddActiveUser($user_data);
		$_SESSION['livechat'] = array ('user_data' => $user_data);
		var_dump($user_data);

		return VIEW_NONE;  
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

	function registerValidators (&$validatorManager, &$controller, &$request, &$user)
	{

	}
	
	function getPrivilege()
	{
		return NULL;
	}
	
	function isSecure()
	{
		return TRUE;    
	}
}

?>