<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to restore user`s pass.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */


class TicketAddThanksView extends View
{
	function &execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer =& $request->getAttribute('SmartyRenderer');
		
		// $renderer->setAttribute('res_message', $request->getParameter('res_message'));
		$renderer->setAttribute('ticket_num', $request->getParameter('ticket_num'));
		$renderer->setAttribute('customer_email', $request->getParameter('customer_email'));
		
		$renderer->setAttribute('pageBody', 'ticketAddthanks.html');
		$renderer->setTemplate('../../user_index.html');
		
		return $renderer;
	}
}
?>