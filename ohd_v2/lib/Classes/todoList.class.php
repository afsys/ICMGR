<?php


/**
 * Tickets list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

class TodoList
{
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
	
	function TodoList()
	{
		$this->db =& sxDB::instance();
	}
		
	/**
	 * Returns array of todo items.
	 *                      
	 * @param     integer    $from    
	 * @param     integer    $cnt     
	 * @param     integer    $where   
	 * @return    array      array of todo items
	 */
	function GetDataListQuery($from, $cnt, $where, $order_by)
	{
		$where = $this->db->compileWhereClause($where);
		$_limit_str = ($cnt) ? "LIMIT $from, $cnt" : "";
		//$order_by = $order_by != "" ? "ORDER BY $order_by, tickets.ticket_id" : "ORDER BY tickets.ticket_id";
		$order_by = '';
		
		// get invoices list
		$r = $this->db->q("
			SELECT 
			   *
			FROM 
			   #_PREF_todo_items

			$where
				
			$order_by
			$_limit_str
		   ");
		
		return $r;
	}

	/**
	 * Returns array of todo items.
	 * @param     integer    $from      
	 * @param     integer    $cnt      
	 * @param     array      $filter    
	 * @return    array      array of todo items
	 */
	function GetDataList($from = 0, $cnt = 0, $where = "", $order_by = "")
	{
		// get items and combine groups
		$r = $this->GetDataListQuery($from, $cnt, $where, $order_by);
		$items = array();
		$prev_index = -1;
		
		while ($data = $this->db->fetchAssoc($r)) {
			$items[] = $data;
		}
		
		return $items;
	}

	
	function GetItemData($tdi_id)
	{
		$res = $this->GetDataListQuery(0, 0, array('tdi_id' => $tdi_id), "");
		$item = $this->db->fetchAssoc($res);
		return is_array($item) ? $item : null;
	}


}

?>