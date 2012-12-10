<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to edit todo item.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Apr 25, 2006
 * @version    1.00 Beta
 */

require_once 'Classes/tickets.class.php';
require_once 'Classes/sx_db_ini.class.php';
require_once 'Classes/sx_db_ini.class.php';
require_once 'Classes/todoItem.class.php';
require_once 'mail.class.php';
	
class TodoItemEditAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db            =& sxDb::instance();
		$tickets       =  new Tickets($db);

		// check if it is unauthenticated user
		if (!$user->isAuthenticated()) {
			$_num   = $request->getParameter('ticket_num');
			$_email = $request->getParameter('customer_email');
			
			$ticket_id = $db->getOne('SELECT * FROM #_PREF_tickets tickets', array('ticket_num' => $_num, 'customer_email' => $_email));
			
			// incorect user data
			if (!is_numeric($ticket_id))
			{
				header('Location: index.php');
				exit();
			}
			
			$_GET['ticket_num']     = $_num;
			$_GET['customer_email'] = $_email;
			$request->setParameter('ticket_id', $ticket_id);
		}
		

        $sys_options = $user->getAttribute('sys_options'); 
		$submit_type = $request->getParameter('submit_type');
		$ticket_id   = $request->getParameter('ticket_id');
		
		if (!empty($submit_type)) {
			$todo_data = $request->getParameter('todo_item');
			$tdi_id = ToDoItem::Save($todo_data);
			//var_dump($todo_data);
			
			
			switch ($submit_type) {
				case 'open_ticket':
					header('Location: index.php?module=Tickets&action=TicketsEdit&ticket_id=' . $todo_data['ticket_id']);
					exit();
					break;
					
				case 'open_todo_list':
					header('Location: index.php?module=ToDo&action=ToDoItemsList');
					exit();
					break;
				
				default:
					header('Location: index.php?module=Tickets&action=TodoItemEdit&tdi_id=' . $tdi_id);
					exit();
					break;
			}
			
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

	function getPrivilege()
	{
		return null;
	}
	
	function isSecure()
	{
		return false;    
	}
}

?>