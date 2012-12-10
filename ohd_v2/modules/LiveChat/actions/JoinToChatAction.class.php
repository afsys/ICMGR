<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Try join to chat and redirect on Chat form on success.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php';
	
class JoinToChatAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		$db       =& sxDb::instance();
		$sid      =  $request->getParameter('sid');
		$user_id  =  $user->getAttribute('user_id');
		$session  = new LcSession($sid);
		
		// is session closed?
		if (!$session->isClosed()) {
			// get user id by sid and agent_id
			$lc_user_id = $db->getOne('SELECT user_id FROM #_PREF_lc_users', array('sid' => $sid, 'agent_id' => $user_id));
			$lc_user = new LcUser($lc_user_id);
			$lc_user_data = $lc_user->getData();
			
			switch ($lc_user_data['status']) {
				case 'requesting':
					break;
				case 'requesting_for_transfer':
					// put login messages
					$logout_user_id = $lc_user_data['data']['requested_by_user_id'];
					$logout_user = new LcUser($logout_user_id);
					$logout_user_data = $logout_user->getData();
					$session->PutMessage($logout_user_id, $logout_user_data['nickname']." logged out", 
					                     array('user_id' => $logout_user_id, 'nickname' => $logout_user_data['nickname']), 
					                     LCM_USER_LOGGED_OUT);
					$logout_user->setStatus('closed');
					break;
					
				case 'chating':
					header('Location: index.php?module=LiveChat&action=ChatWindow');
					exit();

				default:
					ddump($lc_user_data['status']);
					break;
			}
			
			// put login messages
			$session->PutMessage($lc_user_id, $lc_user_data['nickname']." logged in", 
				array('user_id' => $lc_user_id, 'nickname' => $lc_user_data['nickname']), LCM_USER_LOGGED_IN);
			
			$lc_user->setStatus('chating');
			
			// put user data in session
			$_SESSION['livechat'] = array('user_data' => $lc_user_data);
		}
		
		header('Location: index.php?module=LiveChat&action=ChatWindow');
		exit();
	}
	
	function isSecure()
	{
		return false;
	}
}

?>