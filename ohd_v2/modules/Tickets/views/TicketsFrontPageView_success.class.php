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

require_once 'Classes/tickets.class.php';

class TicketsFrontPageView extends View
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
		
		// ASSIGNED TICKETS LIST
		$tickets = new Tickets($db);
		$ticket_where = array(
			'assigned_to' => $user->getAttribute('user_id'), 
			'closed_at'   => 'IS NOT NULL', 
			'status'      => array ('!=', 'Closed')
		);
		$tickets_list = $tickets->GetDataList(0, 0, $ticket_where, 'modified_at_order DESC');
		$renderer->setAttribute('assigned_tickets', $tickets_list);        
		
		// LAST TICKETS LIST
		$user_data = $user->getAttribute('user_data');
		if (!$user_data['is_sys_admin'])
		{
			$ticket_where = array (
				'#DELIM' => 'OR',
				'creator_user_id' => $user->getAttribute('user_id'),
				'assigned_to'   => $user->getAttribute('user_id')
			);
			
			var_dump($ticket_where);
			
			// $next_prev_clause = $user->GetTicketsRightsLimitClause($next_prev_clause);
			
			// check rights
			$user_rights = $user->getAttribute('user_rights');

			if (($user_rights & SR_TL_VIEW_OTHERS) == SR_TL_VIEW_OTHERS) {
				$curr_where = array (
					'assigned_to' => array ('!=', 0),
					'OR' => $ticket_where
				);
				$ticket_where = $curr_where;
			} 

			if (($user_rights & SR_TL_VIEW_UNASSIGNED) == SR_TL_VIEW_UNASSIGNED) {
				$curr_where = array (
					'assigned_to' => 0,
					'OR' => $ticket_where
				);
				$ticket_where = $curr_where;
			}
		}
		else 
			$ticket_where = "";
        
        // last tickets list
        $tickets = new Tickets($db);
        $tickets_list = $tickets->GetDataList(0, 7, $ticket_where, 'modified_at_order DESC');
        $renderer->setAttribute('last_tickets', $tickets_list);        
        
                
        $renderer->setAttribute('pageBody', 'ticketsFrontPage.html');
        $renderer->setTemplate('../../index.html');
        
        return $renderer;
    }
}
?>