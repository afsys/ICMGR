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
 * @created    Dec 10, 2005
 * @version    1.00 Beta
 */

class TicketsDeleteMsgAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		$message_data = array(
			'ticket_id'  => $request->getParameter('ticket_id'),
			'message_id' => $request->getParameter('message_id')
		);
		
		$db->qD('#_PREF_tickets_messages', $message_data);
		
		header('Location: index.php?module=Tickets&action=TicketsEdit&ticket_id='.$message_data['ticket_id']);
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