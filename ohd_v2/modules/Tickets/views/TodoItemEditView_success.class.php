<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to add user.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

require_once 'Classes/sx_db_ini.class.php';
require_once 'Classes/todoList.class.php';
require_once 'Classes/ticket.class.php';
require_once 'Classes/users.class.php';

class TodoItemEditView extends View
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer     =& $request->getAttribute('SmartyRenderer');
		$db           =& sxDb::instance();
		$todoList     =  new TodoList();
		$dbIni        =  new sxDbIni();
		
		$user_options =  $user->GetOptions();
		$sys_options  =  $dbIni->LoadIni(DB_PREF.'sys_options');
		$user_rights  =  $user->getAttribute('user_rights');
		
		$user_id      =  $user->getAttribute('user_id');
		$tdi_id       =  $request->getParameter('tdi_id');
		
		// todo item edit
		if ((int)$tdi_id > 0) {
			$todo_data = $todoList->GetItemData($tdi_id);
			$renderer->setAttribute('todo_item', $todo_data);
			
			$ticket_id = $todo_data['ticket_id'];
		}
		else {
			$ticket_id = $request->getParameter('ticket_id');
			// ?:todo item add
			if ((int)$ticket_id <= 0) {
				die('E:\Projects\Omni\ohd_new\modules\Tickets\views\TodoItemEditView_success.class.php : 47');
			}
		}
		
		// get binded ticket data
		if (!empty($ticket_id )) {
			$ticket = new Ticket($ticket_id);
			$renderer->setAttribute('ticket', $ticket->data);
		}
		
		// item_progresses
		$item_progresses = array();
		$val = 0;
		while ($val <= 100) {
			$item_progresses[$val] = $val == 0 ? "not started yet" : "$val%";
			$val += 10;
		}
		$renderer->setAttribute('item_progresses', $item_progresses);
		
		// assign-to users list
		$users = new Users($db);
		$renderer->setAttribute('users', $users->GetDataList());        
		
		
		
		$renderer->setAttribute('user_rights', $user_rights);
		$renderer->setAttribute('sys_options', $sys_options);
		
		$renderer->setAttribute('pageBody', 'todoItemEdit.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}
}
?>