<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to show tickets statistic.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 14, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'Classes/tickets.class.php';
require_once 'Classes/sx_db_ini.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/users.class.php';

class TicketsStatisticView extends View
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer =& $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		$tickets  = new Tickets($db);
		//$user_options = $user->getAttribute('user_options');
		$user_options =  $user->GetOptions();
		
		// COMMON STATISTIC
		$filters = array (
			'opened' => array (
				'is_in_trash_folder' => array('!=', 1),
				'closed_at' => null,
				'status'    => array('!=', 'Closed')
			),
			'opened_today' => array (
				'is_in_trash_folder' => array('!=', 1),
				'closed_at' => null,
				'status'    => array('!=', 'Closed'),
				'#PLAIN'    => 'TO_DAYS(created_at) = TO_DAYS(NOW())'
			),
			'closed_today' => 'TO_DAYS(closed_at) = TO_DAYS(NOW())',
			'unassigned'   => array (
				'is_in_trash_folder' => array('!=', 1),
				'assigned_to' => 0
			),
			'common' => array (
				'is_in_trash_folder' => array('!=', 1)
			),
			'in_trash' => array (
				'is_in_trash_folder' => 1
			),
		);
		
		// opened tickets
		$cnt = $db->getOne("SELECT COUNT(*) AS count FROM #_PREF_tickets", $filters['opened']);
		$renderer->setAttribute('common_opened', $cnt);
		// opened today tickets count
		$cnt = $db->getOne("SELECT COUNT(*) AS count FROM #_PREF_tickets", $filters['opened_today']);
		$renderer->setAttribute('common_opened_today', $cnt);
		// closed today tickets count
		$cnt = $db->getOne("SELECT COUNT(*) AS count FROM #_PREF_tickets", $filters['closed_today']);
		$renderer->setAttribute('common_closed_today', $cnt);
		// common unassigned tickets count
		$cnt = $db->getOne("SELECT COUNT(*) AS count FROM #_PREF_tickets", $filters['unassigned']);
		$renderer->setAttribute('common_unassigned', $cnt);
		// total tickets count
		$cnt = $db->getOne("SELECT COUNT(*) AS count FROM #_PREF_tickets", $filters['common']);
		$renderer->setAttribute('common_total', $cnt);
		// in_trash
		$cnt = $db->getOne("SELECT COUNT(*) AS count FROM #_PREF_tickets", $filters['in_trash']);
		$renderer->setAttribute('common_in_trash', $cnt);

		
		// OPEN AVG TIME
		$r = $db->q("
			SELECT AVG(unix_timestamp(IFNULL(closed_at, NOW())) - unix_timestamp(created_at)) AS time_difference 
			FROM #_PREF_tickets
		");
		list($secs) = $db->fetchArray($r);
		$days = round($secs/86400);
		$secs = $secs % 86400;
		$hours = round($secs/3600);
		$renderer->setAttribute('avg_open_time', $days.' days, '.$hours.' hours');

		// BREAK DOWNS
		// breakdown by status
		$r = $db->q("
			SELECT 
			   COUNT(*) AS count, 
			   status 
			FROM #_PREF_tickets tickets 
			WHERE is_in_trash_folder != 1
			GROUP BY status 
			ORDER BY status");
		$tickets = array();
		while ($data = $db->fetchAssoc($r)) $tickets[] = $data;
		$renderer->setAttribute('tickets_by_status', $tickets);
		
		// breakdown by priority
		$r = $db->q("
			SELECT 
			   COUNT(*) AS count, 
			   priority 
			FROM #_PREF_tickets tickets 
			WHERE is_in_trash_folder != 1
			GROUP BY priority 
			ORDER BY priority");
		$tickets = array();
		while ($data = $db->fetchAssoc($r)) $tickets[] = $data;
		$renderer->setAttribute('tickets_by_priority', $tickets);

		/*
		echo "<pre>";
		var_dump($prefs['ticket_types']);
		echo "</pre>"; /**/
				

		$renderer->setAttribute('pageBody', 'ticketsStatistic.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}
}
?>