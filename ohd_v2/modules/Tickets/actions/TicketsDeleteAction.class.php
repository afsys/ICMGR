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

error_reporting(E_ALL);
require_once 'Classes/ticket.class.php';
    
class TicketsDeleteAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db          =& sxDb::instance();
		$dbIni       =  new sxDbIni($db);
		$sys_options =  $dbIni->LoadIni(DB_PREF.'sys_options');
		$user_id     =  $user->getAttribute('user_id');
		
		// do action
		$ticket_id = $request->getParameter('ticket_id');
		if ($ticket_id) {
			$ticket = new Ticket($ticket_id);
			
			if ($ticket->data['is_in_trash_folder'] || empty($sys_options['tickets']['trash_on_delete']) || $sys_options['tickets']['trash_on_delete'] == 0) {
				$ticket->Delete();
			}
			else {
				$ticket->TrashIt($user_id); 
			}
				
		}

		$prev_filter = $request->getParameter('prev_filter') ? '&prev_filter=1' : '';
		header('Location: index.php?module=Tickets&action=TicketsList'.$prev_filter);
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
        return NULL;
    }
    
    function isSecure()
    {
        return TRUE;    
    }
}

?>