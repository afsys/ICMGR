<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to add new ticket.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 21, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'Classes/tickets.class.php';

	
class TicketsCloseAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		$is_reopen = $request->getParameter('reopen');
		$sys_options = $user->getAttribute('sys_options');
		
		$user_id = $user->getAttribute('user_id');
		$ticket_id = $request->getParameter('ticket_id');
		$ticket = new Ticket($ticket_id);
		
		if ($is_reopen) {
			$ticket->Open($user_id, $sys_options['tickets']['status_for_reopened']);
		}
		else {
			$ticket->Close($user_id, $sys_options['tickets']['status_for_closed']);
			header('Location: index.php?module=Tickets&action=TicketsList&prev_filter=1');
			die();
		}        
		
		header('Location: index.php?module=Tickets&action=TicketsEdit&ticket_id='. $request->getParameter('ticket_id'));
		die();
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
		return NULL;
	}
	
	function isSecure()
	{
		return TRUE;    
	}
}

?>