<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to view ticket, add messages and 
 * make other ticket operations.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

require_once 'Classes/tickets.class.php';
require_once 'Classes/sx_db_ini.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/groups.class.php';
require_once 'Classes/users.class.php';
require_once 'Classes/timeTracker.class.php';
require_once 'Classes/todoList.class.php';
require_once 'Classes/canned_emails.class.php';

require_once BASE_DIR.'modules/KnowledgeBase/views/KnowledgeBaseViewItem.class.php';

$priorities_order = array();
$statuses_order = array();

class TicketsEditView extends View
{
	function formatTime($time) {
		$days  = floor($time/(3600*24));
		$hours = floor($time/3600-$days*24);
		$mins  = floor($time/60 - $hours*60 - $days*60*24);
		$secs  = floor($time - $mins*60 - $hours*3600 - $days*24*3600);

		$result = "";
		if ($days != 0)  $result .= $days." days ";
		if ($hours != 0) $result .= $hours." hours ";
		if ($mins != 0)  $result .= $mins." min ";
		if ($secs != 0 && ($days == 0 && $hours == 0 && $mins == 0))  $result .= $secs." sec";
		return $result;
	}
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		/* get options from the ini file */
		$config = parse_ini_file(dirname(__FILE__).'/BBCodeParser.ini', true);
		$options = &PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
		$options = $config['HTML_BBCodeParser'];
		unset($options);

		require_once('HTML/BBCodeParser.php');
		$parser = new HTML_BBCodeParser();

		// alias inherited data for easy access
		$renderer =& $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		$dbIni    = new sxDbIni($db);
		$tickets  = new Tickets($db);
		//$user_options   = $user->getAttribute('user_options');
		$user_options =  $user->GetOptions();
		$is_customer    = $user->getAttribute('is_customer') || !$user->isAuthenticated();
		$print_preview  = $request->getParameter('print_preview');
		
		
		$user_id = $user->getAttribute('user_id');
		
		$ticket_id = $request->getParameter('ticket_id');
		if (!$ticket_id) 
		{
			header('Location: index.php?module=Tickets&action=TicketsList');
			die();
		}
		
		// canned emails categories
		$ce = new CannedEmails();
		$renderer->setAttribute('ce_categories', $ce->getCategories());
		
		// set current user id
		$renderer->setAttribute('user_id', $user->getAttribute('user_id'));
		
		// get ticket filelds list
		$ticket_data = $tickets->GetItemData($ticket_id);
		//var_dump($ticket_data['customer_add_emails']);
		//$ticket_data['customer_add_emails'] = preg_replace('/\s+/', ' ', implode(', ', $ticket_data['customer_add_emails']));
		//$ticket_data['description_is_html'] = 1;
		if ($ticket_data['description_is_html'])
		{
			$ticket_data['description'] = $ticket_data['description'];
			//htmlwrap($ticket_data['description'], 110);
		}
		else
		{
			$parser->setText(htmlspecialchars($ticket_data['description']));
			$parser->parse();
			$ticket_data['description'] = $parser->getParsed();
		}
		
		$renderer->setAttribute('ticket', $ticket_data);
		
		// order direction
		$sort_order = isset($user_options['defaults']['messages_order']) && $user_options['defaults']['messages_order'] == 'asc' ? 'ASC' : 'DESC';
		$page_size  = !empty($user_options['defaults']['messages_per_page']) ? $user_options['defaults']['messages_per_page'] : 7;
		$item_index = !empty($_GET['item_index']) && is_numeric($_GET['item_index']) ? $_GET['item_index'] : 0;
		
		$renderer->setAttribute('sort_order', $sort_order);
		
		// MESSAGES, EMAILS, HISTORY and so on...
		if ($is_customer) $messages_where_clause = 'AND m.message_type != \'note\'';
		else $messages_where_clause = '';
				
		$messages_type = $request->getParameter('MessagesType');
		if (!$messages_type) $messages_type = 'Messages';
		$renderer->setAttribute('messages_type', $messages_type);
		
		switch ($messages_type)
		{
			case 'Messages':      $table_name = 'tickets_messages';        break;
			case 'EMails':        $table_name = 'emails_history';          break;
			case 'History':       $table_name = 'tickets_history';         break;
			case 'TimeTracking':  $table_name = 'tickets_time_tracking';   break;
			
			default: die('\modules\Tickets\views\TicketsEditView_success.class.php:70'); break;
		}
		
		
		// MAKE MESSAGES PAGING
		$db->q("SELECT COUNT(*) FROM #_PREF_$table_name m WHERE ticket_id = $ticket_id $messages_where_clause");
		$messages_count = $db->result();
		$renderer->setAttribute('messages_count', $messages_count);
		// make url
		$pages_count = ceil($messages_count/$page_size);
		$curr_page   = floor($item_index/$page_size);
		$tickets_paging = '';
		$_GET['module']    = 'Tickets';
		$_GET['action']    = 'TicketsEdit';
		$_GET['ticket_id'] = $ticket_id;

		for ($i = 0; $i < $pages_count; $i++)
		{
			$_GET['item_index'] = $i * $page_size;
			if ($curr_page == $i) $tickets_paging .= ($i+1) . "&nbsp;";
			else $tickets_paging .= '<a href="?'. http_build_query($_GET) .'">'. ($i+1) .'</a>&nbsp;';
		}
		
		$renderer->setAttribute('tickets_paging', $tickets_paging);
		$renderer->setAttribute('messages_count', $messages_count);
		//echo "item_index: $item_index, curr_page: $curr_page, messages_count: $messages_count, page_size: $page_size, pages_count: $pages_count<br>";
		

		switch ($messages_type)
		{
			case 'Messages': 
				// get messages
				$db->q("
					SELECT 
					   m.message_id,
					   m.message_creator_user_id,
					   m.message_creator_user_name,
					   m.message_type,
					   m.message_subject,
					   m.message_text,
					   m.message_text_is_html,
					   message_atachment_file,
					   message_atachment_name,
					   DATE_FORMAT(m.message_datetime, '%H:%y of %Y-%m-%d') AS message_datetime_formated,
					   m.message_datetime as message_datetime,
					   UNIX_TIMESTAMP(m.message_datetime) as message_unix_datetime,
					   CONCAT(o.user_name, ' ', o.user_lastname) AS message_owner_name
					FROM 
					   #_PREF_tickets_messages m
					   LEFT JOIN #_PREF_users o ON o.user_id = m.message_creator_user_id
					WHERE
					   ticket_id = $ticket_id
					   $messages_where_clause
					ORDER BY 
					   #m.message_datetime $sort_order
					   m.message_id $sort_order
					LIMIT $item_index, $page_size
				");
				
				$messages = array();
				$i = 0;

				//done
				$trans = array_flip(get_html_translation_table());
				while ($message = $db->fetchAssoc()) 
				{
					if ($message['message_creator_user_id'])
					{
						switch ($message['message_type'])
						{
							 case 'ticket': $message['header_style'] = "background-color: #FEF9C7;"; break;
							 case 'note':   $message['header_style'] = "background-color: #90DD95;"; break;
							 default:       $message['header_style'] = "background-color: #DD9090;"; break;
						}


					}
					else
					{
						switch ($message['message_type'])
						{
							 case 'ticket': $message['header_style'] = "background-color: #FEF9C7;"; break;
							 case 'note'  : $message['header_style'] = "background-color: #90DD95;"; break;
							 default:       $message['header_style'] = "background-color: #90AADD;"; break;
						}
					}
					
					if ($message['message_text_is_html']) {
						//$message['message_text'] = wordwrap($message['message_text'], 100, "<br />\n");
					}
					else {
						//remove it 
						$message['message_text'] = strtr($message['message_text'], $trans);
						//
						//$parser->setText(htmlspecialchars($message['message_text']));
						$parser->setText(($message['message_text']));
						$parser->parse();
						$message_text = $parser->getParsed();
						$message['message_text'] = htmlwrap($message_text, 100);
					}
					
 					// @TODO!!
 					$message['message_text'] = preg_replace('/<script.*?>.+?<\/script>/s', '', $message['message_text']);
 					

					if ($i != 0) 
					{
 						$shift = $messages[$i-1]["message_unix_datetime"] - $message["message_unix_datetime"];
 						if ($sort_order == "DESC") {
	 						//from NEW to OLD , set post date of previous message
  						$messages[$i-1]["time_after_last"] = $this->formatTime(abs($shift));
  					} else {
	 						//from OLD to NEW , set post date of current message
  						$message["time_after_last"] = $this->formatTime(abs($shift));
  					}
  				}
  				else
  				{
  					$message["time_after_last"] = false;
  				}
  				
					$messages[$i] = $message;
					$i++;
				}
				$renderer->setAttribute('messages', $messages);
				
				break;
				
			case 'EMails':   
				// get messages
				$db->q("
					SELECT 
					   em.email,
					   em.subj,
					   em.message,
					   DATE_FORMAT(em.rec_date, '%H:%y of %Y-%m-%d') AS message_datetime_formated,
					   CONCAT(o.user_name, ' ', o.user_lastname) AS sent_by_name
					FROM 
					   #_PREF_emails_history em
					   LEFT JOIN #_PREF_users o ON o.user_id = em.posted_by
					WHERE
					   ticket_id = $ticket_id
					ORDER BY 
					   rec_date DESC
					LIMIT $item_index, $page_size
				");
				$emails = array();
				while ($email = $db->fetchAssoc()) 
				{
					$email['header_style'] = "background-color: #D8E08D;";
					$emails[] = $email;
				}
				$renderer->setAttribute('emails', $emails);

				break;
				
			case 'History':   
				// get history
				$db->q("
					SELECT 
					   th.his_notes,
					   DATE_FORMAT(th.rec_date, '%H:%y %Y-%m-%d') AS message_datetime_formated,
					   rec_date,
					   IFNULL(CONCAT(o.user_name, ' ', o.user_lastname), '{$ticket_data['customer_name']}')  AS action_by_name
					FROM 
					   #_PREF_tickets_history th
					   LEFT JOIN #_PREF_users o ON o.user_id = th.user_id
					WHERE
					   ticket_id = $ticket_id
					ORDER BY 
					   rec_date DESC
					LIMIT $item_index, $page_size
				");
				$history_items = array();
				while ($history_item = $db->fetchAssoc()) 
				{
					$history_item['header_style'] = "background-color: #F5F5F5;";
					$history_items[] = $history_item;
				}
				$renderer->setAttribute('history_items', $history_items);

				break;
				
			case 'TimeTracking': 
				$tracker = new TimeTracker($user_id, $ticket_id);
				$tt_items = $tracker->GetDataList();
				$renderer->setAttribute('tt_items', $tt_items);
				break;
			
			default: die('\modules\Tickets\views\TicketsEditView_success.class.php:120'); break;
		}
		
		// get additional fields
		$db->q("
			SELECT 
				ticket_field_caption,
				ticket_field_value
			FROM #_PREF_tickets_products_form_fields ff
			   LEFT JOIN #_PREF_tickets_products_forms_values fv 
                    ON ff.ticket_product_id = fv.ticket_product_id
                    AND ff.ticket_field_id = fv.ticket_field_id
			   WHERE ticket_id = $ticket_id
		");
		
		$ticket_fileds = array();
		while ($item = $db->fetchAssoc()) $ticket_fileds[] = $item;
		$renderer->setAttribute('ticket_fileds', $ticket_fileds);
		//echo "<pre>";
		//var_dump($ticket_fileds);
		
		
		// errors messages
		$message = array();
		if ($request->getAttribute('message')) $message[] = array('message' => $request->getAttribute('message'));
		if (!$ticket_data['ticket_email_customer'])
		{
			$message[] = array('caption' => "<strong style=\"color: red;\">". __('Notice') ."</strong>: ".__("'Copy client when ticket is updated' is uncheked."));
		}
		$renderer->setAttribute('message', $message);

		
		//echo "<pre style='text-align: left;'>";
		//var_dump($prefs);
		//echo "</pre>"; /**/
		
		// assign-to users list
		$users = new Users($db);
		$renderer->setAttribute('users', $users->GetDataList(0, 0, array('is_customer' => 0))); 
		
		// groups
		$groups = new Groups($db);
		$renderer->setAttribute('groups', $groups->GetDataList());        
		
		// user`s rights
		$user_rights = $user->getAttribute('user_rights');
		$renderer->setAttribute('user_rights', $user_rights);    

		// SYSTEM OPTIONS
		$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
		if (!empty($sys_options['tickets_list']['flags'])) $sys_options['tickets_list']['flags'] = explode("\n", $sys_options['tickets_list']['flags']);
		//$sys_options = $user->getAttribute('sys_options');
		$renderer->setAttribute('sys_options',  $sys_options);
		$renderer->setAttribute('user_options', $user_options);
		$renderer->setAttribute('ticket_priorities', $sys_options['ticket_priorities']);
		$renderer->setAttribute('ticket_statuses',   $sys_options['ticket_statuses']);
		$renderer->setAttribute('ticket_types',      $sys_options['ticket_types']);
		if (!empty($sys_options['attachments']['allow']) && $sys_options['attachments']['allow']) $renderer->setAttribute('allow_attachments', 1);


		// GetCategoriesOptionsList
		$kb_items = KB_GetAllItemsList();
		$renderer->setAttribute('kb_items', $kb_items);
		
		$error_message = $request->getParameter('error_message');
		if (is_array($error_message))
		{
			$renderer->setAttribute('message_caption', $error_message['caption']);
			$renderer->setAttribute('message', $error_message['message']);
		}
		
		
		// GET NEXT AND PREV TICKET ITEMS
		if ($user_options['tickets_edit']['quick_navigation_type'] == 'prev_next') {
			$next_prev_clause = $_SESSION['tickets_list_where_clause'];
			$next_prev_clause = $user->GetTicketsRightsLimitClause($next_prev_clause);
			$next_prev_clause = $db->compileWhereClause($next_prev_clause, '');
			$t1_clause = str_replace('tickets.', 't1.', $next_prev_clause);
			$t2_clause = str_replace('tickets.', 't2.', $next_prev_clause);
			if (trim($t1_clause) != "") $next_prev_clause = "AND ($t1_clause) AND ($t2_clause) ";
			else $next_prev_clause = "";
			//$next_prev_clause = " ($t2_clause) ";
			
			$strTicketMessages = strpos ($next_prev_clause, "tickets_messages");
			if ($strTicketMessages !== false) $strTicketMessages = ", #_PREF_tickets_messages";
			else $strTicketMessages = "";
			$next_prev_clause = str_replace ("tickets_messages", "#_PREF_tickets_messages", $next_prev_clause);			
			
			
			/* echo "<pre style=\"text-align: left;\">";
			var_dump($next_prev_clause);
			echo "</pre>"; /**/

			
			// previous item
			/*$prev_ticket_id = $db->getOne("
				SELECT t2.ticket_id
				FROM #_PREF_tickets t1, #_PREF_tickets t2
				WHERE t1.ticket_id = $ticket_id AND t2.ticket_id != $ticket_id
				  AND t2.modified_at >= t1.modified_at
				  #AND t2.ticket_num > t1.ticket_num
				  AND $next_prev_clause
				ORDER BY t2.modified_at ASC, t2.ticket_num ASC
				LIMIT 1
			"); */
			
			// prev
			$prev_ticket_id = $db->q("
				SELECT t2.ticket_id, t2.ticket_num, t2.modified_at
				FROM #_PREF_tickets t1, #_PREF_tickets t2 ".$strTicketMessages."
				WHERE t1.ticket_id = $ticket_id
				  AND t2.modified_at >= t1.modified_at
				  $next_prev_clause
				ORDER BY t2.modified_at ASC, t2.ticket_id DESC
				LIMIT 10
			");
			$curr_ticket_data = $db->fetchAssoc();

			// if have any prev element
			if ($curr_ticket_data) {
				// check second next element for same value by ORDER column
				$prev_ticket_data = $db->fetchAssoc();
				$prev_ticket_id = $prev_ticket_data['ticket_id'];
				
				if ($prev_ticket_data && $curr_ticket_data['modified_at'] == $prev_ticket_data['modified_at']) {
					$prev_ticket_id = $r = $db->getOne("
						SELECT t2.ticket_id
						FROM #_PREF_tickets t1, #_PREF_tickets t2 ".$strTicketMessages."
						WHERE t1.ticket_id = $ticket_id
						  AND t2.modified_at = t1.modified_at
						  AND t2.ticket_id < t1.ticket_id
						  $next_prev_clause
						ORDER BY t2.modified_at ASC, t2.ticket_id DESC
						LIMIT 10
					");
					
					if (!$prev_ticket_id) {
						$prev_ticket_id = $r = $db->getOne("
							SELECT t2.ticket_id
							FROM #_PREF_tickets t1, #_PREF_tickets t2 ".$strTicketMessages."
							WHERE t1.ticket_id = $ticket_id
							  AND t2.modified_at > t1.modified_at
							  $next_prev_clause
							ORDER BY t2.modified_at ASC, t2.ticket_id DESC
							LIMIT 10
						");
					}
				}
				
				$prev_item = $tickets->GetItemData($prev_ticket_id);
				if ($prev_item) list($prev_item['messages_new'], $prev_item['messages_total']) = $tickets->GetUnreadedTicketMessages($user_id, $prev_ticket_id);
				$renderer->setAttribute('ticket_prev', $prev_item);
			}
			
			// next item
			$r = $db->q("
				SELECT t2.ticket_id, t2.ticket_num, t2.modified_at
				FROM #_PREF_tickets t1, #_PREF_tickets t2 ".$strTicketMessages."
				WHERE t1.ticket_id = $ticket_id
				  AND t2.modified_at <= t1.modified_at
				  $next_prev_clause
				ORDER BY t2.modified_at DESC, t2.ticket_id ASC
				LIMIT 10
			");
			
			$curr_ticket_data = $db->fetchAssoc();
			// if have any next element
			if ($curr_ticket_data) {
				// check second next element for same value by ORDER column
				$next_ticket_data = $db->fetchAssoc();
				$next_ticket_id = $next_ticket_data['ticket_id'];
				if ($next_ticket_data && $curr_ticket_data['modified_at'] == $next_ticket_data['modified_at']) {
					$next_ticket_id = $r = $db->getOne("
						SELECT t2.ticket_id
						FROM #_PREF_tickets t1, #_PREF_tickets t2 ".$strTicketMessages."
						WHERE t1.ticket_id = $ticket_id
						  AND t2.modified_at = t1.modified_at
						  AND t2.ticket_id > t1.ticket_id
						  $next_prev_clause
						ORDER BY t2.modified_at DESC, t2.ticket_id
						LIMIT 10
					");
					
					if (!$next_ticket_id) {
						$next_ticket_id = $r = $db->getOne("
							SELECT t2.ticket_id
							FROM #_PREF_tickets t1, #_PREF_tickets t2 ".$strTicketMessages."
							WHERE t1.ticket_id = $ticket_id
							  AND t2.modified_at < t1.modified_at
							  $next_prev_clause
							ORDER BY t2.modified_at DESC, t2.ticket_id
							LIMIT 10
						");
					}
				}
					
				$next_item = $tickets->GetItemData($next_ticket_id);
				if ($next_item) list($next_item['messages_new'], $next_item['messages_total']) = $tickets->GetUnreadedTicketMessages($user_id, $next_ticket_id);
				$renderer->setAttribute('ticket_next', $next_item);
			}
		}
		
		// TODO LIST
		if (defined('F_TODO')) {
			$todoList = new TodoList();
			$todo_items = $todoList->GetDataList(0, 0, array('ticket_id' => $ticket_id));
		}
		
		// sort items by priority and status
		global $priorities_order;
		global $statuses_order;
		$i = 0;
		foreach (array_keys($sys_options['ticket_priorities']) as $v) {
			$priorities_order[$v] = $i;
			$i++;
		}

		foreach (array_keys($sys_options['ticket_statuses']) as $v) {
			$statuses_order[$v] = $i;
			$i++;
		}
		//var_dump($statuses_order);
		
		usort ($todo_items, "ToDoCmp");

		
		
		
		$renderer->setAttribute('todo_items', $todo_items);
		
		
		/*
		// prev item
		$prev_clause = array (
			'AND'         => $next_prev_clause,
			'modified_at' => array ('>', $ticket_data['modified_at_order'])
		);
		
		$order_by_to = $_SESSION['tickets_list_orderby'] ." DESC"; //. (strtoupper($_SESSION['tickets_list_orderto']) == 'ASC' ? 'DESC' : 'ASC') ;
		$prev_item = $tickets->GetDataList(0, 1, $prev_clause, $order_by_to);
		$prev_item = !empty($prev_item[0]) ? $prev_item[0] : null;
		$renderer->setAttribute('ticket_prev', $prev_item); */
		
		/*
		// next item
		$next_clause = array (
			'AND'         => $next_prev_clause,
			'modified_at' => array ('<', $ticket_data['modified_at_order'])
		);
				
		$order_by_to = $_SESSION['tickets_list_orderby'] ." ASC";//. $_SESSION['tickets_list_orderto'];
		$next_item = $tickets->GetDataList(0, 1, $next_clause, $order_by_to);
		$next_item = !empty($next_item[0]) ? $next_item[0] : null;
		$renderer->setAttribute('ticket_next', $next_item);*/
		
		
		// conf
		$renderer->setAttribute('max_desc_height', 130);
		$renderer->setAttribute('is_authenticated', $user->isAuthenticated() ? 1 : 0);
		if ($user->isAuthenticated()) $renderer->setAttribute('hide_top_panel', 1);
		
		// fetch templates
		if ($is_customer)
		{
			$renderer->setAttribute('is_customer', 1);
			if ($print_preview) 
			{
				$renderer->setTemplate('../../index_print.html');
				$renderer->setAttribute('pageBody', 'ticketsEditPrint.html');
			}
			else 
			{
				$renderer->setTemplate('../../user_index.html');
				$renderer->setAttribute('pageBody', 'ticketsEdit.html');
			}
		}
		else
		{
			$renderer->setAttribute('is_customer', 0);
			if ($print_preview) {
				$renderer->setTemplate('../../index_print.html');
				$renderer->setAttribute('pageBody', 'ticketsEditPrint.html');
			}
			else {
				$renderer->setAttribute('pageBody', 'ticketsEdit.html');
				$renderer->setTemplate('../../index.html');
			}
		}
		
		return $renderer;
	}
}

function ToDoCmp($a, $b) 
{
	global $priorities_order;
	global $statuses_order;
	

	if ($statuses_order[$a['status']] == $statuses_order[$b['status']]) return 0;
	else $statuses_order[$a['status']] > $statuses_order[$b['status']] ? 1 : -1;


	if ($priorities_order[$a['priority']] > $priorities_order[$b['priority']]) return 1;
	elseif ($priorities_order[$a['priority']] < $priorities_order[$b['priority']]) return -1;
	else {
	}
}
?>