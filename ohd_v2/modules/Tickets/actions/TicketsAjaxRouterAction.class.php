<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Ajax router for User preferences.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Aug 31, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/ticket.class.php';
 
class TicketsAjaxRouterAction extends AjaxAction
{
	function execute (&$controller, &$request, &$user)
	{
		parent::execute($controller, $request, $user);
		
		
		return VIEW_NONE;
	}
	
	function getTicketsToMerge($params)
	{
		$ticket_id = $params->ticket_id;
		$tickets = new Tickets();
		
		$tickets_list = $tickets->GetDataList(0, 0, $_SESSION['tickets_list_where_clause'], $_SESSION['tickets_list_orderby']." ".$_SESSION['tickets_list_orderto']);
		
		foreach ($tickets_list as $k=>$v) {
			$tickets_list[$k] = array (
				'ticket_id'  => $v['ticket_id'],
				'ticket_num' => $v['ticket_num'],
				'caption'    => $v['caption'] ? $v['caption'] : $v['customer_name']
			);
		}
		$this->res['tickets'] = $tickets_list;
		$this->writeRes();
	}
	
	function saveTicketParams($params)
	{
		$ticket_id = $params->ticket_id;
		$ticket = new Ticket($ticket_id);
		$res = $ticket->UpdateProperty($user_id, 'flags', $params->flags);
		
		$this->writeRes();
	}


	function isSecure()
	{
		return false;   
	}

}

?>