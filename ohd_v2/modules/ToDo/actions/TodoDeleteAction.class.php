<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to delete todo item.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Apr 27, 2006
 * @version    1.00 Beta
 */

require_once 'Classes/todoItem.class.php';
	
class ToDoDeleteAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$tdi_id = $request->getParameter('tdi_id');
		if ($tdi_id)
		{
			$todo = new TodoItem($tdi_id);
			$ticket_id = $todo->data['ticket_id'];
			$todo->Delete();
		}
		
		if (!empty($ticket_id)) header('Location: index.php?module=Tickets&action=TicketsEdit&ticket_id=' . $ticket_id);
		else header('Location: index.php?module=ToDo&action=ToDoItemsList');
		exit();
		
		/* $prev_filter = $request->getParameter('prev_filter') ? '&prev_filter=1' : '';
		header('Location: index.php?module=Tickets&action=TicketsList'.$prev_filter);/*
		exit(); */
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
		return null;
	}
	
	function isSecure()
	{
		return true;    
	}
}

?>