<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Show ChatWindow.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 08, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php';
	
class CloseSessionAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		if (!empty($_SESSION['livechat']['user_data']['user_id'])) {
			$chat_user_id = $request->getParameter('user_id');
			$user_key     = $request->getParameter('user_key');
			
			if ($_SESSION['livechat']['user_data']['user_id'] == $chat_user_id) {
				//$res = LiveChat::CloseUserSession($_SESSION['livechat']['opponent_data']['user_id']);
				$res = LiveChat::CloseSession($chat_user_id, $user_key);
				
				if ($res) {
					LiveChat::AddMessage($_SESSION['livechat']['user_data']['user_id'], $_SESSION['livechat']['user_data']['user_key'],
					                     "{$_SESSION['livechat']['user_data']['user_nickname']} logged out.", null, LCM_OPPONENT_LOGGED_OUT);
					LiveChat::AddMessage($_SESSION['livechat']['sid'], null, "Session closed.", null, LCM_SESSION_CLOSED);
				
					echo "Ok!";
				}
				else {
					echo "Error";
				}
			}
			else {
				echo "Error: incorect user_id value...";
			}
		}
		else {
			echo "Error: lc doesn't started yet...";
		}
			

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
		return null;
	}
	
	function isSecure()
	{
		return false;
	}
}

?>