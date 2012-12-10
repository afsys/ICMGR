<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show tickets list.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */


require_once 'Classes/tickets.class.php';
require_once 'Classes/sx_db_ini.class.php';

class TicketsListAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db          =& sxDb::instance();
		$dbIni       =  new sxDbIni($db);
		$sys_options =  $dbIni->LoadIni(DB_PREF.'sys_options');
		
		
		// check ticket data experation
		if (defined('F_EXPIREDAT_FLD')) {
			$db->q('SELECT ticket_id FROM #_PREF_tickets', array('expired_at' => array('<', 'NOW()'), 'closed_at' => null));
			while ($item = $db->fetchAssoc()) {
				$ticket = new Ticket($item['ticket_id']);
				$ticket->Close(0, !empty($sys_options['tickets']['status_for_closed']) ? $sys_options['tickets']['status_for_closed'] : 'Closed');
			}
		}
		
		// auto-archiving tickets
		if (defined('F_TIME_ARCHIVE_TICKETS') && !empty($sys_options['auto_ticket_arhciving']['archive_after_days']) &&
		    is_numeric($sys_options['auto_ticket_arhciving']['archive_after_days']) && $sys_options['auto_ticket_arhciving']['archive_after_days'] > 0) {
			$status = $sys_options['auto_ticket_arhciving']['status_for_ticket'] ? $sys_options['auto_ticket_arhciving']['status_for_ticket'] : 'Archived';
				
			$db->q('
				UPDATE #_PREF_tickets
				SET
				   is_in_trash_folder = 1,
				   ticket_num         = CONCAT(\''. $sys_options['auto_ticket_arhciving']['prefix'] .'\', ticket_num),
				   status             = \''. $status .'\'
				WHERE 
				   is_in_trash_folder != 1
				   AND closed_at IS NOT NULL
				   AND closed_at < (NOW() - INTERVAL '. $sys_options['auto_ticket_arhciving']['archive_after_days'] .' DAY)
			');
		}
		
		// GROUP ACTIONS TICKETS OPTIONS APPLY
		$group_tickets_options = $request->getParameter('group_tickets_options');
		$sel_ids = $request->getParameter('sel_ids');
		if ($group_tickets_options && count($sel_ids) > 0) {
			$opt_sel_priority = $request->getParameter('opt_sel_priority');
			$opt_sel_status   = $request->getParameter('opt_sel_status');
			$opt_assigned_to  = $request->getParameter('opt_assigned_to');
			$opt_sel_action   = $request->getParameter('opt_sel_action');

			$user_id = $user->getAttribute('user_id');
			
			foreach ($sel_ids as $sel_id=>$v) {
				// $v == on - common ticket
				// $v ==  t - trashed
				
				$ticket = new Ticket($sel_id);
				if ($opt_sel_priority) $ticket->UpdateProperty($user_id, 'priority',    $opt_sel_priority);
				if ($opt_sel_status)   $ticket->UpdateProperty($user_id, 'status',      $opt_sel_status);
				if ($opt_assigned_to)  $ticket->UpdateProperty($user_id, 'assigned_to', $opt_assigned_to);
				

				
				switch ($opt_sel_action) {
					case 'delete':   
						if ($v == 't' || 
							empty($sys_options['tickets']['trash_on_delete']) || 
							$sys_options['tickets']['trash_on_delete'] == 0) {
							$ticket->Delete();          
							break;
						}
					case 'trash_it': 
						$ticket->TrashIt($user_id); 
						break;
				}
			}
			
			$_GET['module'] = 'Tickets';
			$_GET['action'] = 'TicketsList';
			$_GET['prev_filter'] = 1;
			$q = http_build_query($_GET);
			
			//header('Location: index.php?' . $q);
			//exit();
		}
		
		return VIEW_SUCCESS;  
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