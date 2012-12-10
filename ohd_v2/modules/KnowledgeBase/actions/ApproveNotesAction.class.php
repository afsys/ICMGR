<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show users notes list for approvement.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 1, 2005
 * @version    1.00 Beta
 */
    
error_reporting(E_ALL);


    
class ApproveNotesAction extends Action
{
    function execute (&$controller, &$request, &$user)
    {
        // make aliases
        $db =& sxDb::instance();
        

        $submit_approvements = $request->getParameter('submit_approvements');
        if ($submit_approvements )
        {
            $checked     = $request->getParameter('checked');
            $action_type = $request->getParameter('action_type');            
            
            /*echo "<pre>";
            var_dump($action_type);
            var_dump($checked);*/
            
            switch ($action_type)
            {
            	case 'approve':
            		foreach ($checked as $item_id=>$notes_id)
	            	{
	            		$notes_id = implode(',', array_keys($notes_id));
	            		$db->q("UPDATE #_PREF_kb_items_notes SET note_approved = 1 WHERE item_id = $item_id AND note_id IN ($notes_id)");
	            	}
            		break;
            		
            	case 'delete':
            		foreach ($checked as $item_id=>$notes_id)
	            	{
	            		$notes_id = implode(',', array_keys($notes_id));
	            		$db->q("DELETE FROM #_PREF_kb_items_notes WHERE item_id = $item_id AND note_id IN ($notes_id)");
	            	}
            		break;
            		
            }
        }
        
        
        return VIEW_SUCCESS;  
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
        return NULL;
    }
    
    function isSecure()
    {
        return TRUE;    
    }
}

?>