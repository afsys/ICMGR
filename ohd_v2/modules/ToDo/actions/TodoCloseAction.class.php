<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to close todo item.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Apr 27, 2006
 * @version    1.00 Beta
 */

require_once 'Classes/todoItem.class.php';
	
class ToDoCloseAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$tdi_id = $request->getParameter('tdi_id');
		if ($tdi_id)
		{
			$todo      = new TodoItem($tdi_id);
			//$ticket_id = $todo->data['ticket_id'];
			$user_id   = $user->getAttribute('user_id');
			
			$sys_options = $todo->GetSysOptions();
			$todo->Close($user_id, $sys_options['tickets']['status_for_closed']);
		}
		
		//if (!empty($ticket_id)) header('Location: index.php?module=Tickets&action=TicketsEdit&ticket_id=' . $ticket_id);
		//else header('Location: index.php?module=ToDo&action=ToDoItemsList');
		header('Location: index.php?module=Tickets&action=TodoItemEdit&tdi_id=' . $tdi_id);
		exit();
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