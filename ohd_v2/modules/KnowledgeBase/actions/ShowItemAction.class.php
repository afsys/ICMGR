<?php
    
/**
 * ShowItemAction class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    28 September 2005
 */

class ShowItemAction extends Action
{
    function execute (&$controller, &$request, &$user)
    {
        $db =& sxDb::instance();
        
        if (!is_null($request->getParameter('submit_item')))
        {        
            $this->processPostData($db, $controller, $request, $user);
        }         

        return VIEW_SUCCESS;
    }
    
    function processPostData(&$db, &$controller, &$request, &$user)
    {
        $cat_id       = $request->getParameter('cat_id');
        $item_caption = $request->getParameter('item_caption');
        $item_notes   = $request->getParameter('item_notes');

        $item_id = $request->getParameter('item_id');
        
        // insert record
        if ($item_id == null)
        {
            $item_id = $db->getOne('SELECT IFNULL(MAX(item_id)+1,1) FROM #_PREF_kb_items');
            $query = "
                INSERT INTO kb_items (cat_id, item_id, item_caption, item_notes) 
                    VALUES ($cat_id, $item_id, '".  mysql_real_escape_string($item_caption) ."', '".  mysql_real_escape_string($item_notes) ."') ";
        }
        // update record
        else
        {
            $query = "
                UPDATE kb_items 
                SET
                   item_caption = '".  mysql_real_escape_string($item_caption) ."', 
                   item_notes   = '".  mysql_real_escape_string($item_notes) ."',
                   cat_id       = $cat_id
                WHERE
                   item_id = $item_id
            ";
        }
        
        echo "<pre>";
        var_dump ($query);
        echo "</pre>";        
        
        $res = $db->query($query);        

        header('location: index.php?module=KnowledgeBase&action=Categories&cat_id='.$cat_id);
        die();
        //$controller->forward('KnowledgeBase', 'Categories');
    }
    
    function getDefaultView (&$controller, &$request, &$user)
    {
        return VIEW_SUCCESS;
    }

    function handleError (&$controller, &$request, &$user)
    {
        // don't handle errors, just redirect to error 404 action
        $controller->forward(ERROR_404_MODULE, ERROR_404_ACTION);

        return VIEW_NONE;
    }

    function registerValidators (&$validatorManager, &$controller, &$request, &$user)
    {

    }
    
    function getPrivilege()
    {
        return null;
    }

    function isSecure()
    {
        return false;   
    }
}

?>