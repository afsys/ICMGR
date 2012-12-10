<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show tickets statistic.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 14, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'Classes/tickets.class.php';
	
class TicketsStatisticAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// check rights
		$user_rights = $user->getAttribute('user_rights');
		if (($user_rights & SR_TICKETS_STATISTIC) != SR_TICKETS_STATISTIC)
		{
			header('Location: index.php?module=System&action=HaveNoRights');
			die();
		}		
		
		// make aliases
		$db =& sxDb::instance();
		$tickets = new Tickets($db);
		
		$submit_type = $request->getParameter('submit_type');
		$ticket_id   = $request->getParameter('ticket_id');
		
		if ($submit_type && $ticket_id)
		{
			$ticket_data  = $tickets->GetItemData($ticket_id);
			$message_data = array (
				'ticket_id'         => $ticket_id
			);
			$message_id = $db->getNextId('#_PREF_tickets_messages', 'message_id');
			
			$message_data['message_creator_user_id'] = $user->getAttribute('user_id');
			$message_data['message_id']            = $message_id;
			$message_data['message_text']          = $request->getParameter('new_'.$submit_type);
			$message_data['message_datetime']      = 'NOW()';
			$message_data['message_type']          = $submit_type;
			
			$db->qI('#_PREF_tickets_messages', $message_data);
			
			header('Location: index.php?module=Tickets&action=TicketsEdit&ticket_id='.$ticket_id);
			die();
		}

		return VIEW_SUCCESS;  
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;
	}

	function handleError (&$controller, &$request, &$user)
	{
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
		return true;    
	}
}

?>