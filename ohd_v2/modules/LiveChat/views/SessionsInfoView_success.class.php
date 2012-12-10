<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to show sessions info.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Sep 18, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php'; 
 
class SessionsInfoView extends View
{
	function & execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db       =& sxDb::instance();
		$renderer =& $request->getAttribute('SmartyRenderer');
		
		// get opened sessions list
		$r = $db->q(
			"SELECT * FROM #_PREF_lc_sessions", 
			array (
				'#WHERE' => array('closed' => 0),
				'#ORDER' => 'created_at DESC',
			)
		);
		$sessions = array();
		while ($sid = $db->fetchAssoc($r)) {
			$session = new LcSession($sid['sid']);
			$users = $session->getUsers();
			
			$sdata = $session->getData();
			foreach ($users as $k=>$v) {
				$users[$k] = $users[$k]->getData();
			}
			$sdata['users'] = $users;
			$sessions[] = $sdata;
		}
		
		$renderer->setAttribute('sessions',  $sessions);
		
		// last closed sessions
		$r = $db->q(
			"SELECT * FROM #_PREF_lc_sessions", 
			array (
				'#WHERE' => array('closed' => 1),
				'#ORDER' => 'created_at DESC',
				'#LIMIT' => '5',
			)
		);
		$sessions = array();
		while ($sid = $db->fetchAssoc($r)) {
			$session = new LcSession($sid['sid']);
			$users = $session->getUsers();
			
			$sdata = $session->getData();
			foreach ($users as $k=>$v) {
				$users[$k] = $users[$k]->getData();
			}
			$sdata['users'] = $users;
			$sessions[] = $sdata;
		}
		
		$renderer->setAttribute('closed_sessions', $sessions);
		
		$renderer->setAttribute('pageBody', 'sessions_info.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}
}

?>
