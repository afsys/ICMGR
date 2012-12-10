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
 * @created    Jun 06, 2006
 * @version    1.00 Beta
 */

require_once 'Classes/tickets.class.php';
	
class TicketsCvsExportAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		$db =& sxDb::instance();
		
		$ticket_where = $_SESSION['tickets_list_where_clause'];
		$ticket_where = $user->GetTicketsRightsLimitClause($ticket_where);
		
		$tickets = new Tickets($db);
		$tickets_list = $tickets->GetDataList(null, null, $ticket_where);
		
		$keys = array (
			'ticket_num'                 =>    'Id',
			'modified_at'                =>    'Last Update',
			'due_date'                   =>    'Due Date',
			'caption'                    =>    'Caption',
			'customer_name'              =>    'Client',
			'ticket_product_caption'     =>    'Product',
			'type'                       =>    'Action',
			'priority'                   =>    'Priority',
			'status'                     =>    'Status',
			'ticket_assigned_to_name'    =>    'Ticket Owner'
		);
		
		//var_dump($tickets_list);
		
		$cvs = array();
		$str_cvs = "";
		
		$a = array();
		foreach ($keys as $key_id=>$key_name) {
			$a[] = '"'. str_replace('"', '""', $key_name) .'"';
			
		}
		$str_cvs .= implode(';', $a)."\n";
		
		
		//var_dump($tickets_list);
		
		foreach ($tickets_list as $ticket_data) {
			$a = array();
			foreach ($keys as $key_id=>$key_name) {
				$a[$key_id] = '"'. str_replace('"', '""', $ticket_data[$key_id]) .'"';
			}
			$cvs[] = $a;
			$str_cvs .= implode(';', $a)."\n";
		}

		// output data
		//header("Content-Type: text/plain");
		$file_date = date('j-m-y');
		header("Content-Type: application/text");
		header("Content-Disposition: attachment; filename=ohd_tickets_{$file_date}.csv");
		echo $str_cvs;
		
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_NONE;
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

/*
$res = $DB->query('SELECT * FROM #__OSS_users '.$whereClause);
if (!DB::isError($res))
{
	$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
	if ($row)
	{
		echo implode(';', array_keys($row))."\n";
		array_walk($row,array(&$this,'quoteCsv'));
		echo implode(';', array_values($row))."\n";
	}

	while ($row = $res->fetchRow())
	{
		array_walk($row,array(&$this,'quoteCsv'));
		echo implode(';', $row)."\n";
	}
}

function quoteCsv(&$item,$key) {
	$item = '"'.str_replace('"','""',$item).'"';
}
*/


?>