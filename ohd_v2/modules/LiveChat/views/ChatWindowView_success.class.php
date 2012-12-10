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
require_once ("xajax.inc.php");

class ChatWindowView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer      =& $request->getAttribute('SmartyRenderer');
		$db            =& sxDb::instance();
		$is_customer   =  $user->getAttribute('is_customer') || !$user->isAuthenticated();
		
		if (empty($_SESSION['livechat'])) {
			die('Couldn\'t find LiveChat session');
		}
		
		if ('closed' == $_SESSION['livechat']['user_data']['status']) {
			die('Current LiveChat session is closed');
		}
		
		// TODO: заглушка для $_SESSION['livechat']['user_data']['status'] - убрать
		$lc_user = new LcUser($_SESSION['livechat']['user_data']['user_id']);
		$lc_user_data = $lc_user->getData();
		if ('closed' === $lc_user_data['status']) die('Current LiveChat session is closed [dummy]'); /**/
		
		// тоже заглушка, когда продолжает быть открытым окно с чатом, но статус уже другой
		if ('requesting_for_trasfer' === $lc_user_data['status']) {
			header('Location: index.php?module=LiveChat&action=JoinToChat&user_id='.$lc_user_data['user_id']);
			die();
		}
		
		
		$renderer->setAttribute('chat_session', $_SESSION['livechat']);
		
		// get techs list
		$techs = LiveChat::GetAgentsList();
		$renderer->setAttribute('techs', $techs);
		
		// predefined responses list
		$db->q('SELECT * FROM #_PREF_lc_pred_responses ORDER BY resp_caption');
		$pred_responses = array();
		while ($item = $db->fetchAssoc()) {
			$pred_responses[] = $item;
		}
		$renderer->setAttribute('pred_responses', $pred_responses);

		// get transcript
		$messages = array();
		$lc_session = new LcSession($_SESSION['livechat']['user_data']['sid']);
		//$transcript = $lc_session->getMessages();
		//$messages = $transcript['messages'];
		
		// частный случай для чата 2х людей, когда первый запрашивает второго
		// вывести сообщение что идет запрос
		$lc_users = $lc_session->GetUsers('active', true);
		if (count($lc_users) == 2) {
			if (($lc_users[0]->data['status'] == 'chating' && $lc_users[1]->data['status'] == 'requesting') ||
			    ($lc_users[1]->data['status'] == 'chating' && $lc_users[0]->data['status'] == 'requesting')) {
				
				$agent_nickname = $lc_users[0]->data['status'] == 'chating' ? $lc_users[1]->data['nickname'] : $lc_users[0]->data['nickname'];
				
				$messages[] = array (
					'message'         => 'Requesting for agent ('. $agent_nickname  .'), please wait',
					'service_command' => 'NO_IN_DB_COMMAND'
				);
			}
		}
		
		
		$renderer->setAttribute('messages', $messages); 
		$renderer->setAttribute('is_customer', $is_customer);
		$renderer->setTemplate('chat_window.html');
		
		return $renderer;
	}

}
?>