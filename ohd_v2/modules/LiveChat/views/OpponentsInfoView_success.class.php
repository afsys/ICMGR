<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to ...
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Oct 06, 2006
 * @version    1.00 Beta
 */
 
class OpponentsInfoView extends View
{
	function & execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db       =& sxDb::instance();
		$renderer =& $request->getAttribute('SmartyRenderer');

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
		
		
		
		$renderer->setAttribute('ticket_id',  $ticket_id);
		$renderer->setAttribute('message_id', $message_id);
		
		$renderer->setAttribute('pageBody', 'opponents_info.html');
		$renderer->setTemplate('../index.html');
		
		return $renderer;
	}
}

?>
