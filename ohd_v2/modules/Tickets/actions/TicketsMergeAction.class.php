<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to merge 2 tickets.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 31, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'Classes/ticket.class.php';
	
class TicketsMergeAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		
		$child_ticket_id  = $request->getParameter('child_ticket_id');
		$parent_ticket_id = $request->getParameter('parent_ticket_id');
		
		if (empty($parent_ticket_id)) {
			$parent_ticket_num = $request->getParameter('parent_ticket_num');
			$parent_ticket_id = $db->getOne('SELECT ticket_id FROM #_PREF_tickets', array('ticket_num' => $parent_ticket_num));
			
			ddump($parent_ticket_id);
		}
		
		$ticket = new Ticket($parent_ticket_id);
		if ($ticket->appendTicket($child_ticket_id)) {
			// go to the new ticket page
			$ticket_id = $parent_ticket_id;
		}
		else {
			// come back to ticket page
			$ticket_id = $child_ticket_id;
		}
		
		header("Location: index.php?module=Tickets&action=TicketsEdit&ticket_id=$ticket_id");
		exit();
	}

	
	function getPrivilege()
	{
		return null;
	}
	
	function isSecure()
	{
		return true;    
	}
}

?>