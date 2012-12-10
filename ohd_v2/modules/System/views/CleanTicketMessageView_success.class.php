<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * View for ...
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jul 20, 2006
 * @version    1.00 Beta
 */
 
require_once 'lib/Classes/sx_emails_html_cleaner.class.php';

class CleanTicketMessageView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// init items
		$db       =& sxDb::instance();
		$renderer =& $request->getAttribute('SmartyRenderer');
		
		$ticket_id  = $request->getParameter('ticket_id');
		$message_id = $request->getParameter('message_id');
		
		if ($ticket_id && $message_id) {
			$db->q('SELECT * FROM #_PREF_tickets_messages', array('ticket_id' => $ticket_id, 'message_id' => $message_id));
			$msg_data = $db->fetchAssoc();
			$msg = $msg_data['message_text'];
			
			$cleaner = new sxEmailsHtmlCleaner();
			$cleaner->setSourceHtml($msg);
			$cleaner->clearHtml();
			$cleared_msg =  $cleaner->getClearHtml();
			
			if ($request->getParameter('clear_message')) {
				$msg = '123';
			}
			
			$renderer->setAttribute('message_original', $msg);
			$renderer->setAttribute('message_cleaned',  $cleared_msg);
		}
		
		$renderer->setAttribute('ticket_id',  $ticket_id);
		$renderer->setAttribute('message_id', $message_id);
		
		$renderer->setAttribute('pageBody', 'clean_ticket_message.html');
		$renderer->setTemplate('../../index.html');

		return $renderer;
	}

}

?>
