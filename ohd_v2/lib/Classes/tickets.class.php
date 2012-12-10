<?php

require_once 'ticket.class.php';
require_once 'products.class.php';

/**
 * Tickets list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

class Tickets
{
	var $TF_OPENED = array(1, 2, 3);
	
	
	/**
	 * Database object
	 * @var sxDB
	 */
	var $db = null;
	
	/**
	 * Data container
	 * @var array
	 */
	var $data = null;
	
	/**
	 * Systems options cashe.
	 * @var array
	 */
	var $sys_options = null;
	
	function Tickets()
	{
		$this->db =& sxDB::instance();
	}
		
	/**
	 * Метод возврацает дескриптор запроса для набора объектов Group в  
	 * заданном диапазоне. Вспомогательная функция.                       
	 * @param     integer    $from    начала диапазона                    
	 * @param     integer    $cnt     ширина диапазона                    
	 * @param     integer    $where   дополнительные ограничения на поиск 
	 * @return    array      массив объектов Invoice
	 */
	function GetDataListQuery($from, $cnt, $where, $order_by, $joinMsgs)
	{
		$where = $this->db->compileWhereClause($where);
		$_limit_str = ($cnt) ? "LIMIT $from, $cnt" : "";
		$order_by = $order_by != "" ? "ORDER BY $order_by, tickets.ticket_id" : "ORDER BY tickets.ticket_id";
		
		// escalation evaluate expression
		$sys_options = $this->getSysOptions();
		if (!empty($sys_options['tickets_list']['escalation_after_hours']) || !empty($sys_options['tickets_list']['escalation_after_mins'])) {
			$hours = !empty($sys_options['tickets_list']['escalation_after_hours']) ? $sys_options['tickets_list']['escalation_after_hours'] : 0;
			$mins  = !empty($sys_options['tickets_list']['escalation_after_mins'])  ? $sys_options['tickets_list']['escalation_after_mins']  : 0;
			
			$escalation_row = "
				IF (assigned_to = 0 && created_at + INTERVAL \"$hours:$mins\" HOUR_MINUTE < NOW(), 
				       UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(created_at + INTERVAL \"$hours:$mins\" HOUR_MINUTE), 
				       0)";
		}
		else $escalation_row = 0;
		
		// get invoices list
		$r = $this->db->q("
			SELECT 
			   tickets.ticket_id,
			   tickets.ticket_num,
			   tickets.group_id,
			   tickets.ticket_product_id,
			   tickets.ticket_product_ver,
			   tickets.creator_user_id,
			   tickets.assigned_to,
			   tickets.caption,
			   tickets.description,
			   tickets.description_is_html,
			   tickets.customer_name, 
			   tickets.customer_email,
			   tickets.customer_add_emails,
			   tickets.customer_phone,
			   tickets.customer_last_open_date,
			   tickets.carbon_copy_email,
			   tickets.last_message_posted_by_admin,
			   (TO_DAYS(NOW()) - TO_DAYS(tickets.customer_last_open_date)) AS customer_last_open_days,
			   tickets.status,
			   tickets.priority,
			   tickets.type,
			   tickets.due_date,
			   tickets.ticket_email_customer,
			   CONCAT(p.ticket_product_caption, ' ', IFNULL(ticket_product_ver, '')) AS ticket_product_caption,
			   tickets.created_at AS created_at,
			   tickets.expired_at AS expired_at,
			   tickets.modified_at AS modified_at,
			   tickets.closed_at AS closed_at,
			   CONCAT(o.user_name, ' ', o.user_lastname)             AS ticket_owner_name,
			   CONCAT(a.user_name, ' ', a.user_lastname)             AS ticket_assigned_to_name,
			   IF(a.user_id = '' || a.user_id = 0, NULL,  a.user_id) AS ticket_assigned_to_id,
			   modified_at AS modified_at_order,
			   created_at  AS created_at_order,
			   tickets.hash_code,
			   tickets.add_options,
			   tickets.flags,
			   tickets.is_in_trash_folder,
			
			   $escalation_row AS escalation_interval,
			   
			   # ex options
			   tickets.ex_opt_exclude_from_common_list
			
			FROM 
			   #_PREF_tickets tickets
			   LEFT JOIN #_PREF_users o ON o.user_id = tickets.creator_user_id
			   LEFT JOIN #_PREF_users a ON a.user_id = tickets.assigned_to
			   LEFT JOIN #_PREF_tickets_products p ON tickets.ticket_product_id = p.ticket_product_id ".
			   ($joinMsgs ? "LEFT JOIN #_PREF_tickets_messages tickets_messages ON tickets_messages.ticket_id = tickets.ticket_id" : "").
			"
			$where
			
			$order_by
			$_limit_str
		   ");
		
		return $r;
	}

	/**
	 * Метод возврацает набор объектов Group в заданном диапазоне.
	 * @param     integer    $from      начала диапазона
	 * @param     integer    $cnt       ширина диапазона        
	 * @param     array      $filter    массив ограничений на выборку элементов вида ('fieldname' => 'value', ...)
	 * @return    array      массив объектов Invoice
	 */
	function GetDataList($from = 0, $cnt = 0, $where = "", $order_by = "", $joinMsgs = false)
	{
		//$where = $this->db->compileWhereClause($filter);
		
		/*// make where clause
		$where = "";
		foreach ($filter as $f_name => $f_value) {
			$where .= " AND $f_name LIKE '%$f_value%' ";
		}*/

		// get items and combine groups
		$r = $this->GetDataListQuery($from, $cnt, $where, $order_by, $joinMsgs);
		$items = array();
		$prev_index = -1;
		
		while ($data = $this->db->fetchAssoc($r)) {
			
			// preview
			$data['preview'] = empty($data['description']) ? null : ($data['description_is_html'] ? strip_tags($data['description']) : strip_tags($data['description']));
			$data['preview'] = substr($data['preview'], 0, 255);
			$data['preview'] = htmlspecialchars($data['preview']);
			
			$data['carbon_copy_email']   = empty($data['carbon_copy_email'])   ? array() : unserialize($data['carbon_copy_email']);
			$data['add_options']         = empty($data['add_options'])         ? array() : unserialize($data['add_options']);
			$data['customer_add_emails'] = empty($data['customer_add_emails']) ? array() : explode(',', $data['customer_add_emails']);
			
			$items[] = $data;
		}
		
		return $items;
	}

	
	function GetItemData($ticket_id)
	{
		$res = $this->GetDataListQuery(0, 0, array('ticket_id' => $ticket_id), "");
		$item = $this->db->fetchAssoc($res);
		if ($item) {
			$item['carbon_copy_email']   = empty($item['carbon_copy_email'])   ? array() : unserialize($item['carbon_copy_email']);
			$item['add_options']         = empty($item['add_options'])         ? array() : unserialize($item['add_options']);
			$item['customer_add_emails'] = empty($item['customer_add_emails']) ? array() : explode(',', $item['customer_add_emails']);
		}
		
		return is_array($item) ? $item : null;
	}
	
	function GetItem($ticket_id)
	{
		$data = $this->GetItemData($ticket_id);
		return new Ticket($data);
	}
	
	/**
	 * Generates unique ID from 100000 to 1000000
	 * @return    integer   unique ID
	 */
	function GenerateUniqId()
	{
		$db =& sxDB::instance();
		$sys_options = Tickets::GetSysOptions();
		
		switch ($sys_options['common']['ticket_num_gen_type']) {
			case 'simple_seq':
				if (!empty($sys_options['tickets']['last_ticket_gen_num']) && is_numeric($sys_options['tickets']['last_ticket_gen_num'])) {
					$ticket_num = $sys_options['tickets']['last_ticket_gen_num'];
				}
				else {
					$ticket_num = 1;
				}
				
				$ticket_num = sprintf("%06d", $ticket_num);
				while (true) {
					$db->q("SELECT COUNT(*) FROM #_PREF_tickets WHERE ticket_num = '$ticket_num'");
					$cnt = $db->result();
					if ($cnt == 0) break;
					$ticket_num++;
					$ticket_num = sprintf("%06d", $ticket_num);
				}
				
				$dbIni = new sxDbIni();
				$dbIni->saveIni(DB_PREF.'sys_options', array('tickets' => array('last_ticket_gen_num' => (int)$ticket_num)));
				break;
				
			case 'simple_seq_with_year':
				if (!empty($sys_options['tickets']['last_ticket_gen_year_num']) && is_numeric($sys_options['tickets']['last_ticket_gen_year_num'])) {
					$ticket_num_part = $sys_options['tickets']['last_ticket_gen_year_num'];
				}
				else {
					$ticket_num_part = 1;
				}
				
				$ticket_num = sprintf("%s%04d", date('y'), $ticket_num_part);
				while (true) {
					$db->q("SELECT COUNT(*) FROM #_PREF_tickets WHERE ticket_num = '$ticket_num'");
					$cnt = $db->result();
					if ($cnt == 0) break;
					$ticket_num_part++;
					$ticket_num = sprintf("%s%04d", date('y'), $ticket_num_part);
				}
				
				$dbIni = new sxDbIni();
				$dbIni->saveIni(
					DB_PREF.'sys_options', 
					array('tickets' => array('last_ticket_gen_year_num' => $ticket_num_part, 'last_ticket_gen_year_prev' => date('y')))
				);
				break;
			
			case 'date_based':
				// unique date-based number
				$ticket_num = date('YmdHis');
				while (true) {
					$db->q("SELECT COUNT(*) FROM #_PREF_tickets WHERE ticket_num = '$ticket_num'");
					$cnt = $db->result();
					if ($cnt == 0) break;
					$ticket_num = $ticket_num + 1;
				}
				break;
				
			default:
				// unique digit in [100000, 1000000]
				$ticket_num = rand(100000, 1000000);
				while (true) {
					$db->q("SELECT COUNT(*) FROM #_PREF_tickets WHERE ticket_num = '$ticket_num'");
					$cnt = $db->result();
					if ($cnt == 0) break;
					$ticket_num = rand(100000, 1000000);
				}
				break;
		}
		
		return $ticket_num;
	}
	
	/**
	 * Returns array of system options
	 */
	function GetSysOptions()
	{
		if (is_array($this->sys_options)) return $this->sys_options;
		$dbIni = new sxDbIni();        
		return $this->sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
	}
	
	/**
	 * Returns count of unreaded by user ticket messages and total messages count.
	 * @param     integer    $user_id     user_id
	 * @param     integer    $ticket_id   ticket_id        
	 * @return    array      ($unreaded, $total)
	 */
	function GetUnreadedTicketMessages($user_id, $ticket_id)
	{
		// total messages and readed count
		
		$rIn = $this->db->q("SELECT COUNT(ticket_id) AS total FROM #_PREF_tickets_messages WHERE ticket_id = $ticket_id");
		$mgs_res1 = $this->db->fetchAssoc($rIn);

		$rIn = $this->db->q("
			SELECT
			   SUM(tm.message_datetime > utp.last_view_time) AS new
			FROM #_PREF_tickets_messages tm
			   LEFT JOIN #_PREF_users_tickets_props utp ON tm.ticket_id = utp.ticket_id AND utp.user_id = $user_id
			WHERE tm.ticket_id = $ticket_id
			");
		$mgs_res2 = $this->db->fetchAssoc($rIn);

		$rIn = $this->db->q("SELECT MAX(ticket_id) AS readed FROM #_PREF_users_tickets_props WHERE ticket_id = $ticket_id AND user_id = $user_id");
		$mgs_res3 = $this->db->fetchAssoc($rIn);

		return array($mgs_res3['readed'] ? $mgs_res2['new'] : $mgs_res1['total'], $mgs_res1['total']);		
		
		/*
		$rIn = $this->db->q("
			SELECT
			   COUNT(tm.ticket_id) AS total, 
			   SUM(tm.message_datetime > utp.last_view_time) AS new, 
			   MAX(utp.ticket_id) AS readed
			FROM #_PREF_tickets_messages tm
			   LEFT JOIN #_PREF_users_tickets_props utp ON tm.ticket_id = utp.ticket_id AND utp.user_id = $user_id
			WHERE tm.ticket_id = $ticket_id
			");
		
		$mgs_res = $this->db->fetchAssoc($rIn);
		return array($mgs_res['readed'] ? $mgs_res['new'] : $mgs_res['total'], $mgs_res['total']);
		*/
	}
	
}

?>