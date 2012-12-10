<?PHP
    
/**
 * AddCategoryAction class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    26 September 2005
 */

class EditCategoryAction extends Action
{
    function execute (&$controller, &$request, &$user)
    {
        $db =& sxDb::instance();
        if (!is_null($request->getParameter('submit_category')))
        {
            $this->processPostData($db, $controller, $request, $user);
        }         

        return VIEW_SUCCESS;
    }
    
    function processPostData(&$db, &$controller, &$request, &$user)
    {
        $cat_caption = $request->getParameter('cat_caption');
        $cat_notes   = $request->getParameter('cat_notes');
        
        $cat_parent_id = $request->getParameter('cat_parent_id');
        if (!is_numeric($cat_parent_id)) $cat_parent_id = 0;
        
        $cat_id = $request->getParameter('cat_id');        
        
        $cat_data = array (
        	'cat_parent_id' => $cat_parent_id,
        	'cat_caption'   => $cat_caption,
        	'cat_notes'     => $cat_notes
        );        
        
        // add new category
        if ($cat_id == null)
        {

            $res = $db->qI('#_PREF_kb_categories', $cat_data);
        }
        // update category info
        else
        {
            $res = $db->qI('#_PREF_kb_categories', $cat_data, 'UPDATE', array('cat_id' => $cat_id));     
        }
        
            
        header('location: index.php?module=KnowledgeBase&action=Categories&cat_id='.$cat_parent_id);
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
        return true;   
    }
}

?>