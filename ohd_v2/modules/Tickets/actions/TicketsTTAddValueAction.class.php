<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to save time tracking options.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Feb 15, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);    
require_once 'Classes/sx_db_ini.class.php';   
require_once 'Classes/timeTracker.class.php';   

	
class TicketsTTAddValueAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		$dbIni = new sxDbIni($db);
		
		$user_id    = $request->getParameter('user_id');
		$ticket_id  = $request->getParameter('ticket_id');
		$tt_id      = $request->getParameter('tt_id');
		$tt_type    = $request->getParameter('tt_type');
		$val        = $request->getParameter('val');
		
		if (!empty($user_id) && !empty($ticket_id) && !empty($tt_id) && !empty($tt_type) && !empty($val))
		{
			$method = 'add' . $tt_type;
			$tt = new TimeTracker($user_id, $ticket_id);
			$tt->$method($tt_id, $val);
		}
		
		if (empty($ticket_id))
		{
			header("Location: index.php?module=Tickets&action=TicketsList&set_filter=1&filter[special]=opened");
			exit();
		}
		else
		{
			header("Location: index.php?module=Tickets&action=TicketsEdit&ticket_id=$ticket_id&MessagesType=TimeTracking");
			exit();
		}
	
		return VIEW_NONE;  
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_NONE;
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