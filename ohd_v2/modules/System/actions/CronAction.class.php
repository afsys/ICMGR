<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Action for cron actions.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Apr 04, 2006
 * @version    1.00 Beta
 */
 
require_once 'modules/EmailPiping/classes/ticket_delivery.inc.php';
require_once 'modules/EmailPiping/classes/email_piping_config.inc.php';

class CronAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		$db          =& sxDb::instance();
		$dbIni       =  new sxDbIni($db);
		$sys_options =  $dbIni->LoadIni(DB_PREF.'sys_options');

		$make = $request->getParameter('make');
		$make = explode(',', $make);
		echo "<pre>";
		
		// make piping delivery
		// http://ohd/index.php?module=System&action=Cron&make=piping
		if (in_array('piping', $make)) {
			$results = TicketDelivery::MakeFullDelivery($user);
			echo "Piping:\n";
			var_dump($results);
		}
		
		// make auto archiving job
		// http://ohd/index.php?module=System&action=Cron&make=auto_archiving
		if (in_array('auto_archiving', $make)) {
			$results = TicketDelivery::MakeFullDelivery($user);
			echo "Auto archiving:\n";

			if (defined('F_TIME_ARCHIVE_TICKETS') && !empty($sys_options['auto_ticket_arhciving']['archive_after_days']) &&
			    is_numeric($sys_options['auto_ticket_arhciving']['archive_after_days']) && $sys_options['auto_ticket_arhciving']['archive_after_days'] > 0) {
				$status = $sys_options['auto_ticket_arhciving']['status_for_ticket'] ? $sys_options['auto_ticket_arhciving']['status_for_ticket'] : 'Archived';
					
				$db->q('
					UPDATE #_PREF_tickets
					SET
					   is_in_trash_folder = 1,
					   ticket_num = CONCAT(\''. $sys_options['auto_ticket_arhciving']['prefix'] .'\', ticket_num),
					   status = \''. $status .'\'
					WHERE 
					   is_in_trash_folder != 1
					   AND closed_at IS NOT NULL
					   AND closed_at < (NOW() - INTERVAL '. $sys_options['auto_ticket_arhciving']['archive_after_days'] .' DAY)
				');
				echo "done...\n";
			}
		}
		
		return VIEW_NONE;
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;
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