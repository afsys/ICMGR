<?php
	
/**
 * Users list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Sep 30, 2005
 * @version    1.00 Beta
 */

class Users
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
		
	
	function Users()
	{
		$this->db =& sxDB::instance();
	}
		
	/**
	 * Метод возврацает дескриптор запроса для набора объектов Invoice в  
	 * заданном диапазоне. Вспомогательная функция.                       
	 * @param     integer    $from    начала диапазона                    
	 * @param     integer    $cnt     ширина диапазона                    
	 * @param     integer    $where   дополнительные ограничения на поиск 
	 * @return    array      массив объектов Invoice
	 */
	function GetDataListQuery($from, $cnt, $where)
	{
		$_limit_str = ($cnt) ? "LIMIT $from, $cnt" : "";
		
		// get invoices list
		$r = $this->db->q("
		   SELECT 
			  u.user_id,
			  u.user_login,
			  u.user_pass,
			  u.user_name,
			  u.user_lastname,
			  u.user_email,
			  u.user_enabled,
			  u.is_sys_admin,
			  u.user_rights,
			  u.lc_priority,
			  g.group_id,
			  g.group_caption
		   FROM #_PREF_users u
			  LEFT JOIN #_PREF_users_groups ug ON u.user_id = ug.user_id
			  LEFT JOIN #_PREF_groups g ON ug.group_id = g.group_id
		   $_limit_str
		   $where
		   ");
		
		return $r;
	}

	/**
	 * Метод возврацает набор объектов Invoice в заданном диапазоне.
	 * @param     integer    $from      начала диапазона
	 * @param     integer    $cnt       ширина диапазона        
	 * @param     array      $filter    массив ограничений на выборку элементов вида ('fieldname' => 'value', ...)
	 * @return    array      массив объектов Invoice
	 */
	function GetDataList($from = 0, $cnt = 0, $filter = array())
	{
		// make where clause
		$where = "";
		foreach ($filter as $f_name => $f_value) {
			$where .= " AND $f_name = '$f_value' ";
		}
		if ($where != "") $where = "WHERE 1=1 $where";

		// get items and combine groups
		$r = $this->GetDataListQuery($from, $cnt, $where);
		$items = array();
		$groups = array();
		$prev_index = -1;
		
		while ($data = $this->db->fetchAssoc($r)) 
		{
			
			if ($prev_index == -1 || $items[$prev_index]['user_id'] != $data['user_id'])
			{
				$item = $data;
				$item['groups'] = array(array('group_id' => $data['group_id'], 'group_caption' => $data['group_caption']));
				$items[] = $item;
				$prev_index++;
			}
			else
			{
				$items[$prev_index]['groups'][] = array('group_id' => $data['group_id'], 'group_caption' => $data['group_caption']);
			}
		}
		
		
		/*echo "<pre style='text-align: left;'>";
		var_dump($items);
		echo "</pre>";/**/
		
		return $items;
	}

    function GetUserData($user_id)
    {
        // TODO: check
        if (!$user_id) return;
        $user = $this->GetDataList(0, 0, array('u.user_id' => $user_id));
        $r =  is_array($user) ? $user[0] : null;
        return $r;
    }


}

?>