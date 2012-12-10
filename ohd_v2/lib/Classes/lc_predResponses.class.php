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
		
	
	function Tickets()
	{
		$this->db =& sxDB::instance();
	}
		
	/**
	 * ¦ץ×‏פ ע‏קע¨נ¡נץ× פץ¸ת¨רÿ×‏¨ קנÿ¨‏¸נ פû  ‎נס‏¨נ ‏ס·ץת×‏ע Group ע  
	 * קנפנ‎‎‏ü פרנÿנק‏‎ץ. T¸ÿ‏ü‏ףנ×ץû¹‎נ  ¯÷‎ת¡ר .                       
	 * @param     integer    $from    ‎נ¢נûנ פרנÿנק‏‎נ                    
	 * @param     integer    $cnt     °ר¨ר‎נ פרנÿנק‏‎נ                    
	 * @param     integer    $where   פ‏ÿ‏û‎ר×ץû¹‎vץ ‏ף¨נ‎ר¢ץ‎ר  ‎נ ÿ‏ר¸ת 
	 * @return    array      üנ¸¸רע ‏ס·ץת×‏ע Invoice
	 */
	function GetDataListQuery($from, $cnt, $where, $order_by)
	{
		$where = $this->db->compileWhereClause($where);
		$_limit_str = ($cnt) ? "LIMIT $from, $cnt" : "";
		$order_by = $order_by != "" ? "ORDER BY $order_by, tickets.ticket_id" : "ORDER BY tickets.ticket_id";
		
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
			   tickets.customer_phone,
			   tickets.customer_last_open_date,
			   tickets.last_message_posted_by_admin,
			   (TO_DAYS(NOW()) - TO_DAYS(tickets.customer_last_open_date)) AS customer_last_open_days,
			   tickets.status,
			   tickets.priority,
			   tickets.type,
			   tickets.due_date,
			   tickets.ticket_email_customer,
			   CONCAT(p.ticket_product_caption, ' ', IFNULL(ticket_product_ver, '')) AS ticket_product_caption,
			   tickets.created_at AS created_at,
			   tickets.modified_at AS modified_at,
			   tickets.closed_at AS closed_at,
			   CONCAT(o.user_name, ' ', o.user_lastname)             AS ticket_owner_name,
			   CONCAT(a.user_name, ' ', a.user_lastname)             AS ticket_assigned_to_name,
			   IF(a.user_id = '' || a.user_id = 0, NULL,  a.user_id) AS ticket_assigned_to_id,
			   modified_at AS modified_at_order,
			   created_at  AS created_at_order,
			   
			   # ex options
			   tickets.ex_opt_exclude_from_common_list
			
			FROM 
			   #_PREF_tickets tickets
			   LEFT JOIN #_PREF_users o ON o.user_id = tickets.creator_user_id
			   LEFT JOIN #_PREF_users a ON a.user_id = tickets.assigned_to
			   LEFT JOIN #_PREF_tickets_products p ON tickets.ticket_product_id = p.ticket_product_id
			   
			$where
				
			$order_by
			$_limit_str
		   ");
		
		return $r;
	}

	/**
	 * ¦ץ×‏פ ע‏קע¨נ¡נץ× ‎נס‏¨ ‏ס·ץת×‏ע Group ע קנפנ‎‎‏ü פרנÿנק‏‎ץ.
	 * @param     integer    $from      ‎נ¢נûנ פרנÿנק‏‎נ
	 * @param     integer    $cnt       °ר¨ר‎נ פרנÿנק‏‎נ        
	 * @param     array      $filter    üנ¸¸רע ‏ף¨נ‎ר¢ץ‎רש ‎נ עvס‏¨ת÷ ¤ûץüץ‎×‏ע ערפנ ('fieldname' => 'value', ...)
	 * @return    array      üנ¸¸רע ‏ס·ץת×‏ע Invoice
	 */
	function GetDataList($from = 0, $cnt = 0, $where = "", $order_by = "")
	{
		//$where = $this->db->compileWhereClause($filter);
		
		/*// make where clause
		$where = "";
		foreach ($filter as $f_name => $f_value) {
			$where .= " AND $f_name LIKE '%$f_value%' ";
		}*/

		// get items and combine groups
		$r = $this->GetDataListQuery($from, $cnt, $where, $order_by);
		$items = array();
		$prev_index = -1;
		
		while ($data = $this->db->fetchAssoc($r)) {
			
			// preview
			$data['preview'] = empty($data['description']) ? null : ($data['description_is_html'] ? strip_tags($data['description']) : strip_tags($data['description']));
			$data['preview'] = substr($data['preview'], 0, 255);
			$data['preview'] = htmlspecialchars($data['preview']);
			
			$items[] = $data;
		}
		
		return $items;
	}

	
	function GetItemData($ticket_id)
	{
		$res = $this->GetDataListQuery(0, 0, array('ticket_id' => $ticket_id), "");
		$item = $this->db->fetchAssoc($res);
		return is_array($item) ? $item : null;
	}
	
	function GetItem($ticket_id)
	{
		$data = $this->GetItemData($ticket_id);
		return new Ticket($data);
	}
	

}

?>