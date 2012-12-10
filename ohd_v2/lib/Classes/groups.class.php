<?php
    
/**
 * groups list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Sep 30, 2005
 * @version    1.00 Beta
 */

class Groups
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
        
    
    function Groups()
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
    function GetDataListQuery($from, $cnt, $where)
    {
        $_limit_str = ($cnt) ? "LIMIT $from, $cnt" : "";
        
        // get invoices list
        $r = $this->db->q("
           SELECT 
              group_id,
              group_caption,
              group_comment
           FROM #_PREF_groups
           ",
           $where);
        
        return $r;
    }

    /**
     * Метод возврацает набор объектов Group в заданном диапазоне.
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
            $where .= " AND $f_name LIKE '%$f_value%' ";
        }

        // get items and combine groups
        $r = $this->GetDataListQuery($from, $cnt, $where);
        $items = array();
        $prev_index = -1;
        
        while ($data = $this->db->fetchAssoc($r)) $items[] = $data;
        
        return $items;
    }

	
	function GetGroupData($group_id)
	{
		$res = $this->GetDataListQuery(0, 0, array('group_id' => $group_id));
		$group = $this->db->fetchAssoc($res);
		return is_array($group) ? $group : null;
	}
		
}

?>