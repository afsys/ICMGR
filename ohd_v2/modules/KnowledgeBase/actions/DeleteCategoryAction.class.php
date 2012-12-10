<?PHP
    
/**
 * DeleteCategoryAction class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    27 September 2005
 */

class DeleteCategoryAction extends Action
{
    var $db;
    function execute (&$controller, &$request, &$user)
    {
        $SysConfig = $user->getAttribute('SysConfig');
        $this->db =& sxDb::instance();
        
        $cat_id = $request->getParameter('cat_id');
        if (is_numeric($cat_id)) 
        {
            // get paret item id
            $parent_id = $this->db->getOne("SELECT cat_parent_id FROM #_PREF_kb_categories WHERE cat_id = $cat_id");        
            if ($parent_id == null) $parent_id = 0;
                           
            // delete items        
            $this->deleteCategoriesRecursively(array($cat_id));
            
            header('location: index.php?module=KnowledgeBase&action=Categories&cat_id='.$parent_id);
            die();
        }
        
        header('location: index.php?module=KnowledgeBase&action=Categories');
        die();        
    }
    
    function deleteCategoriesRecursively($cats)
    {
        /*echo '<pre>';    
        var_dump($cats);
        echo '</pre>';*/
        if (count($cats) == 0) return;
        $subitems = array();
        
        foreach ($cats as $cat)
        {
            // get child categories
            $query = "SELECT cat_id FROM #_PREF_kb_categories WHERE cat_parent_id = $cat";
            $res = $this->db->query($query);
            while ($itm = $this->db->fetchAssoc()) $subitems[] = $itm['cat_id'];            
     
        }        
        
        $this->deleteCategoriesRecursively($subitems);
        
        // build in-clause
        $in_clause = "";
        foreach ($cats as $cat)
        {
            if ($in_clause == "") $in_clause .= $cat;
            else $in_clause .= ", $cat";
        }
        
        // delete sum-items
        $res = $this->db->query("DELETE FROM #_PREF_kb_items WHERE cat_id IN ($in_clause)");
        $res = $this->db->query("DELETE FROM #_PREF_kb_categories WHERE cat_id IN ($in_clause)");        
    }
    
   
}

?>