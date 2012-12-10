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
require_once 'Classes/sx_db_ini.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/users.class.php';
require_once 'modules/EmailPiping/classes/ticket_delivery.inc.php';
require_once 'modules/EmailPiping/classes/email_piping_config.inc.php';
require_once 'Classes/Pager2.class.php'; 

class TicketsListView extends View
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
		$dbIni        =  new sxDbIni($db);
		$user_options =  $user->GetOptions();
		$sys_options  =  $dbIni->LoadIni(DB_PREF.'sys_options');
		$is_customer  =  $user->getAttribute('is_customer') || !$user->isAuthenticated();
		
		
		$user_id = $user->getAttribute('user_id');
		$ticket_where = "";

		$set_filter  = $request->getParameter('set_filter');
		$prev_filter = $request->getParameter('prev_filter');
		
		// set filter
		// (!$set_filter && !empty($_SESSION['tickets_list_filter']['filter']))
		if ($set_filter || $prev_filter) {
			if (!empty($_POST['filter'])) $filter = $_POST['filter'];
			else $filter = $request->getParameter('filter');

			if (isset($filter['common']) && is_array($filter['common'])) $filter = $filter['common'];
			
			//if (!$set_filter) $filter = $_SESSION['tickets_list_filter']['filter'];
			if ($prev_filter) $filter = $_SESSION['tickets_list_filter']['filter'];

			$_GET['prev_filter'] = 1;
			$renderer->setAttribute('prev_filter', 1);
			
			// clear empty items
			foreach ($filter as $k=>$v) if (empty($v)) unset($filter[$k]);
			
			__('');
			$renderer->setAttribute('message_caption', __('Filter criteria').': ');
			if (empty($filter['special'])) {
				$rel_type = $request->getParameter('rel_type');
				if (empty($filter['#DELIM']) && !empty($rel_type)) $filter['#DELIM'] = $rel_type;
				$ticket_where = $filter;
				
				$search_value = str_replace('WHERE ', '', $db->compileWhereClause($filter));
				
				// compile where clase in text form (ONLY FOR SIMPLE/LINEAR FILTER )

				switch ($search_value) {
					case 'tickets.ticket_product_id':
						$message = 'Ticket Product';
						$_SESSION['tickets_list_filter_caption'] = null;
						break;
					default:
						if (!empty($_SESSION['tickets_list_filter_caption'])) $message = $_SESSION['tickets_list_filter_caption'];
						else $message = $search_value;
						
						break;
				}

				$renderer->setAttribute('message', $message);
			}
			
			else {

				$filters = array (
					'opened' => array (
						'tickets.is_in_trash_folder' => array('!=', 1),
						'tickets.closed_at' => null,
						'tickets.status'    => array('!=', 'Closed'),
						// TEMPORARY
						'tickets.ex_opt_exclude_from_common_list' => 0,
					),
					'opened_today' => array (
						'tickets.is_in_trash_folder' => array('!=', 1),
						'tickets.closed_at' => null,
						'tickets.status'    => array('!=', 'Closed'),
						'#PLAIN'    => 'TO_DAYS(tickets.created_at) = TO_DAYS(NOW())'
					),
					'closed_today' => 'TO_DAYS(tickets.closed_at) = TO_DAYS(NOW())',
					'unassigned'   => array (
						'tickets.is_in_trash_folder' => array('!=', 1),
						'tickets.assigned_to' => 0
					),
					'common' => array (
						'tickets.is_in_trash_folder' => array('!=', 1)
					),
					'in_trash' => array (
						'tickets.is_in_trash_folder' => 1
					),
					'quick_filter' => array()
				);
				
				$ticket_where = $filters[$filter['special']];
				
				switch ($filter['special'])
				{
					case 'opened':
						// auto-refresh
						if (!empty($user_options['defaults']['page_autorefresh']) && $user_options['defaults']['page_autorefresh'] > 0)
						{
							$text = $user_options['defaults']['page_autorefresh']." sec";
							/* if ($user_options['defaults']['page_autorefresh_check_piping']) {
								$text .= " (E-Mail Piping Enabled";
								
								$cnt = 0;
								$results = TicketDelivery::MakeFullDelivery($user);
								foreach ($results as $r) {
									$cnt += $r['delivered'];
								}
								
								//$err_message = $Delivery->get_error_message();
								$err_message = '';
								if ($err_message == 'Can\'t get listing (possibly there is no new emails)') $err_message = 'no new emails';
								if ($cnt) $err_message = "$cnt messages delivered";
								if ($err_message) $text .= ": $err_message";
								
								$text .= ")";
							} */
							
							$renderer->setAttribute('page_autorefresh', $text);
							$renderer->setAttribute('page_autorefresh_time', $user_options['defaults']['page_autorefresh']);
						}

						if (!is_null($request->getParameter('ticket_product_id')) && -1 != $request->getParameter('ticket_product_id')) {
							$ticket_where['tickets.ticket_product_id'] = $request->getParameter('ticket_product_id');
						}
						$renderer->setAttribute('message', __('Opened Tickets'));
						$_SESSION['tickets_list_filter_caption'] = null;
						break;
						
					case 'opened_today':
						$renderer->setAttribute('message', __('Opened Today Tickets'));
						$_SESSION['tickets_list_filter_caption'] = null;
						break;
						
					case 'closed_today':
						$renderer->setAttribute('message', __('Closed Today Tickets'));
						$_SESSION['tickets_list_filter_caption'] = null;
						break;
						
					case 'unassigned':
						$renderer->setAttribute('message', __('Unasigned Tickets'));
						$_SESSION['tickets_list_filter_caption'] = null;
						break;
						
					case 'common';
						break;
						
					case 'in_trash':
						$renderer->setAttribute('message', __('Tickets In Trash Folder'));
						$_SESSION['tickets_list_filter_caption'] = null;
						break;
						
					case 'quick_filter':
						$filter_ex_params = $request->getParameter('filter_ex_params');
						if (empty($filter_ex_params)) $filter_ex_params = @$_SESSION['tickets_list_filter_params'];
						
						$curr_filter = null;
						foreach ($sys_options['quick_filters'] as $qf) {
							if ($qf['props']['name'] == $filter_ex_params) {
								$curr_filter = $qf;
								break;
							}
						}
						
						if ($curr_filter) {
							$renderer->setAttribute('message', __($curr_filter['props']['name']));
							$_SESSION['tickets_list_filter_caption'] = null;
							$ticket_where = $curr_filter['criteria'];
							ApplyExtraFilterValue($ticket_where);
							if (!empty($ticket_where['#DELIM']) && count($ticket_where) == 1) $ticket_where = array();
						}
						else {
							$renderer->setAttribute('message', __('Unknown quick filter or filter params....'));
							$_SESSION['tickets_list_filter_caption'] = null;
						}
							
						$_SESSION['tickets_list_filter_params'] = $filter_ex_params;
						break;
						
					default:
						$renderer->setAttribute('message', '');
						$_SESSION['tickets_list_filter_caption'] = null;
						die('E:\Projects\Omni\ohd_new\modules\Tickets\views\TicketsListView_success.class.php: 121 - unknow special filter');
						break;
				}
			}
			$_SESSION['tickets_list_filter'] = array('filter' => $filter);
			
		}
		// SEARCH FILTER
		else
		{
			// search criteria
			$search_value = trim($request->getParameter('search_value'));
			$search_value2 = trim($request->getParameter('search_value2'));
			if ($search_value) {
				// search by date
				if (preg_match('/^\d{1,2} \w{3}( \d{2,4})?$/', $search_value) && ($search_timestamp = strtotime($search_value)) !== false) {
					$db->q(
						'SELECT DISTINCT ticket_id FROM #_PREF_tickets_messages', 
						array('UNIX_TIMESTAMP(DATE_FORMAT(message_datetime, "%Y-%m-%d"))' => $search_timestamp)
					);
					
					$ids = array();
					while ($data = $db->fetchAssoc()) {
						$ids[] = $data['ticket_id'];
					}
					
					$ticket_where = array (
						'#DELIM' => 'OR',
						'tickets.ticket_id' => $ids,
						'UNIX_TIMESTAMP(DATE_FORMAT(tickets.modified_at, "%Y-%m-%d"))' => $search_timestamp,
					);
					
					$search_value = $search_value . ' <span style="color: #7D7D7D;">(tickets updated and having messages on defined date)</span>';
				}
				// ticket id 
				else if (is_numeric($search_value)) {
					$ticket_where = array (
						'#DELIM' => 'OR',
						'tickets.ticket_id'   => $search_value,
						'tickets.ticket_num'  => $search_value
					);
				}
				// fulltext search
				else {
					$ticket_where = array (
						'#DELIM' => 'OR',
						'tickets.caption'        => array ('LIKE', "%$search_value%"),
						'tickets.description'    => array ('LIKE', "%$search_value%"),
						'tickets.customer_name'  => array ('LIKE', "%$search_value%"),
						'tickets.customer_email' => array ('LIKE', "%$search_value%") 
					);
					
					if (defined('F_MANUAL_TICKET_NUM')) $ticket_where['tickets.ticket_num'] = array ('LIKE', "%$search_value%");
					
					//$db->q('SELECT ticket_id FROM tickets', $where_clause);
				}
				
				$renderer->setAttribute('message', $search_value);
				$renderer->setAttribute('message_caption', 'Search criteria: ');
				
			}
			else if ($search_value2) {
				$search_value = $search_value2;
				$ticket_where = array (
					'#DELIM' => 'OR',
					'tickets_messages.message_subject'        => array ('LIKE', "%$search_value%"),
					'tickets_messages.message_text'    => array ('LIKE', "%$search_value%"),
					'AND' => array('tickets.is_in_trash_folder' => 0)
				);
				
				if (defined('F_MANUAL_TICKET_NUM')) $ticket_where['tickets.ticket_num'] = array ('LIKE', "%$search_value%");
					
					//$db->q('SELECT ticket_id FROM tickets', $where_clause);				
				
				$renderer->setAttribute('message', $search_value);
				$renderer->setAttribute('message_caption', 'Search criteria: ');
				
			}			
			
			$_SESSION['tickets_list_filter_caption'] = $search_value;
			$_SESSION['tickets_list_filter']         = array('filter' => $ticket_where);
		}

		// GET TICKETS LIST
		// order clause
		$order_keys = array (
			'id'           => 'tickets.ticket_id',
			'created'      => 'created_at_order',
			'updated'      => 'modified_at_order',
			'assigned_to'  => 'ticket_assigned_to_name',
			'type'         => 'type',
			'subj'         => 'tickets.caption',
			'product'      => 'ticket_product_caption',
			'priority'     => 'tickets.priority',
			'status'       => 'tickets.status',
			'due_date'     => 'tickets.due_date',
			'owner'        => 'CONCAT(o.user_name, \' \', o.user_lastname) ',
			'client'       => 'customer_name'
		);
		
		
		if (!empty($_GET['orderby']) && isset($order_keys[$_GET['orderby']])) {
			$orderby = $_GET['orderby'];
			$orderto = (($orderby == $user_options['defaults']['tickets_list_orderby']) && ($user_options['defaults']['tickets_list_orderto'] == 'DESC')) ? 'ASC' : 'DESC';
			$user_options['defaults']['tickets_list_orderby'] = $orderby;
			$user_options['defaults']['tickets_list_orderto'] = $orderto;
			$user->setAttribute('user_options', $user_options);
			
			// save options to DB
			$dbIni = new sxDbIni($db);
			$dbIni->saveIni(DB_PREF.'users_options', $user_options, array('user_id' => $user_id));
		}
		else {
			$orderby = !empty($user_options['defaults']['tickets_list_orderby']) ? $user_options['defaults']['tickets_list_orderby'] : 'id';
			$orderto = !empty($user_options['defaults']['tickets_list_orderto']) ? $user_options['defaults']['tickets_list_orderto'] : 'ASC';
		}
		
		
		$ticket_where_no_userrights = $ticket_where;
		$ticket_where = $user->GetTicketsRightsLimitClause($ticket_where);
		
		// dump($db->compileWhereClause($ticket_where));
		
		// page URI
		unset($_GET['orderby']);
		$renderer->setAttribute('URI', http_build_query($_GET));
		
		// PAGING
		// conf
		$page_size = (int)(@$user_options['defaults']['tickets_per_page']);
		if ($page_size < 10) $page_size = 10;
		$page = !empty($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 0;
		// total count
		// $tickets_count = $db->getOne("SELECT COUNT(*) FROM #_PREF_tickets tickets", $ticket_where);
		if (!$search_value2) $tickets_count = $db->getOne("SELECT COUNT(*) FROM #_PREF_tickets tickets", $ticket_where);
		else $tickets_count = $db->getOne("SELECT COUNT(distinct tickets.ticket_id) FROM #_PREF_tickets tickets ".
			"LEFT JOIN #_PREF_tickets_messages tickets_messages on tickets_messages.ticket_id=tickets.ticket_id", $ticket_where);		
		
		$renderer->setAttribute('tickets_count', $tickets_count);

		// NEW PAGING
		$_GET['prev_filter'] = 1;
		$pager = new Pager2($tickets_count, $page_size, new pagerHtmlRenderer());
		$pager -> setDelta(5);
		$pager -> setFirstPagesCnt(3);
		$pager -> setLastPagesCnt(3);
		$pager -> setPageVarName("page");
		$pages = $pager->render();
		$renderer->setAttribute('pages', $pages);

		
		if (empty($orderby)) $orderby = 'id';
		if (empty($orderto)) $orderto = 'ASC';
		
		// store value for next-prev items
		$_SESSION['tickets_list_where_clause'] = $ticket_where_no_userrights;
		$_SESSION['tickets_list_orderby']      = $order_keys[$orderby];
		$_SESSION['tickets_list_orderto']      = $orderto;
		
		// get tickets
		$tickets = new Tickets($db);

		$tickets_list = $tickets->GetDataList($page*$page_size, $page_size, $ticket_where, "{$order_keys[$orderby]} $orderto", ($search_value2 ? true : false));
		foreach ($tickets_list as $ticket_key=>$ticket_data) {
			// total messages and readed count
			list($ticket_data['messages_new'], $ticket_data['messages_total']) = $tickets->GetUnreadedTicketMessages($user_id, $ticket_data['ticket_id']);
			
			// last modified by
			$ticket_data['last_update_by'] = $db->getOne("
				SELECT IF(user_id > 0, CONCAT(u.user_name, ' ', u.user_lastname), message_creator_user_name)
				FROM #_PREF_tickets_messages tm
				   LEFT JOIN #_PREF_users u ON tm.message_creator_user_id = u.user_id
				WHERE tm.ticket_id = {$ticket_data['ticket_id']}
				ORDER BY message_datetime DESC
				LIMIT 1
				");
			
			$tickets_list[$ticket_key] = $ticket_data;
		}
		$renderer->setAttribute('tickets', $tickets_list);    
		$renderer->setAttribute('orderby', $orderby);    
		$renderer->setAttribute('orderto', $orderto);    
		
		/*echo "<pre style='text-align: left;'>";
		var_dump($tickets_list);
		echo "</pre>"; /**/
				
		// assign-to users list
		$users = new Users($db);
		$renderer->setAttribute('users', $users->GetDataList());
		
		// SET STATUSES, PRIORITIES VALUES
		$renderer->setAttribute('ticket_priorities', $sys_options['ticket_priorities']);
		$renderer->setAttribute('ticket_statuses',   $sys_options['ticket_statuses']);
		$renderer->setAttribute('ticket_types',      $sys_options['ticket_types']);
		$renderer->setAttribute('sys_options',       $sys_options);
		$renderer->setAttribute('user_options',      $user_options);
		
		// products
		$products = new Products($db);
		$products_list = $products->GetDataList();
		$renderer->setAttribute('products_list', $products_list);

		// user`s rights
		$user_rights = $user->getAttribute('user_rights');
		$renderer->setAttribute('user_rights', $user_rights);                    
		
		$renderer->setAttribute('pageBody', 'ticketsList.html');

		
		if ($user->isAuthenticated()) $renderer->setAttribute('hide_top_panel', 1);
		if ($is_customer) {
			$renderer->setAttribute('is_customer', 1);
			$renderer->setTemplate('../../user_index.html');
		}
		else {
			$renderer->setAttribute('is_customer', 0);
			$renderer->setTemplate('../../index.html');
		}
		
		return $renderer;
	}
}


function ApplyExtraFilterValue(&$filter) {
	global $user;
	$user_id = $user->getAttribute('user_id');
	
	foreach ($filter as $k=>$v) {
		if (is_array($v)) {
			ApplyExtraFilterValue($filter[$k]);
		}
		elseif ($filter[$k] == -1) {
			$filter[$k] = $user_id;
		}
			
	}
}

?>