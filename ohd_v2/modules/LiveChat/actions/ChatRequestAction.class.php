<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Show chat request user form.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php';
	
class ChatRequestAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		$db =& sxDB::instance();
		
		$group_id      = $request->getParameter('group_id');
		$user_nickname = $request->getParameter('user_nickname');
		$user_formdata = $request->getParameter('user_data');
		
		if (is_array($user_formdata)) {
			
			// get free angents
			$agents = LiveChat::GetAgentsList('avaiable', $group_id);
			
			if (count($agents) == 0) {
				$request->setParameter('lc_forward',    true);
				$request->setParameter('user_nickname', $user_nickname);
				$request->setParameter('user_formdata', $user_formdata);
				
				$controller->forward('Tickets', 'TicketsAdd');
				return VIEW_NONE; 

				die('\modules\LiveChat\actions\ChatRequestAction.class.php : 45 - there is no any avaiable agent - add ticket ');
			}
			
			// create session
			$session  =& LiveChat::CreateSession(
				$group_id,
				array (
					'name'     => $user_nickname,
					'question' => $user_formdata['question']
				)
			);

			// add user to session
			$customer =& $session->AddUser($user_nickname, $user_formdata, null, 'chating');
			// add user loging status and question
			$session->PutMessage($customer->user_id, "$user_nickname logged in", 
				array('user_id' => $customer->user_id, 'nickname' => $user_nickname), LCM_USER_LOGGED_IN);
			$session->PutMessage($customer->user_id, $user_formdata['question']);

			// put user data in session
			$_SESSION['livechat'] = array('user_data' => $customer->getData());
			
			// add available agent to session
			$customer =& $session->AddUser(null, null, $agents[0]['user_id'], 'requesting');
			
			header('Location: index.php?module=LiveChat&action=ChatWindow');
			exit();
		}
		
		return VIEW_SUCCESS;
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