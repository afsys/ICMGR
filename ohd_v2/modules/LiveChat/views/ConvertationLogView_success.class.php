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
require_once 'Classes/Pager2.class.php'; 


class ConvertationLogView extends View
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
		$db       =& sxDb::instance();

		// get curr group logs
		$sid = $request->getParameter('sid');
		if ($sid) {
			$renderer->setAttribute('sid', $sid);
			$lc_session = new LcSession($sid);
			$renderer->setAttribute('transcript', $lc_session->getMessages());
			
			list($requester) = $lc_session->GetUsers('requester', true);
			$requester->data['chat_requests'] = 1;
			$renderer->setAttribute('requester', $requester->data);
		}
		
		$renderer->setAttribute('pageBody', 'convertation_log.html');
		$renderer->setTemplate('../index.html');
		
		return $renderer;
	}

}
?>