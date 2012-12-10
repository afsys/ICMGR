<?php
    
/**
 * Products list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Sep 30, 2005
 * @version    1.00 Beta
 */

class Products
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
        
    
    function Products()
    {
		$this->db =& sxDB::instance();
    }
        
    /**
     * ����� ���������� ���������� ������� ��� ������ �������� Product �  
     * �������� ���������. ��������������� �������.                       
     * @param     integer    $from    ������ ���������                    
     * @param     integer    $cnt     ������ ���������                    
     * @param     integer    $where   �������������� ����������� �� ����� 
     * @return    array      ������ �������� Invoice
     */
    function GetDataListQuery($from, $cnt, $where)
    {
        $_limit_str = ($cnt) ? "LIMIT $from, $cnt" : "";
        
        // get invoices list
        $r = $this->db->q("
           SELECT 
              ticket_product_id,
              ticket_product_caption,
              ticket_product_desc,
              ticket_product_redirect_url,
              ticket_product_email_customer,
              ticket_product_ver_enabled,
              ticket_product_ver_list,
              default_tech
           FROM 
              #_PREF_tickets_products
           ",
           $where);
        
        return $r;
    }

    /**
     * ����� ���������� ����� �������� Group � �������� ���������.
     * @param     integer    $from      ������ ���������
     * @param     integer    $cnt       ������ ���������        
     * @param     array      $filter    ������ ����������� �� ������� ��������� ���� ('fieldname' => 'value', ...)
     * @return    array      ������ �������� Invoice
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

    
    function GetProductData($ticket_product_id)
    {
        $res = $this->GetDataListQuery(0, 0, array('ticket_product_id' => $ticket_product_id));
        $group = $this->db->fetchAssoc($res);
        return is_array($group) ? $group : null;
    }
        
}

?>