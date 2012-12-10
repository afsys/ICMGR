<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to manage prefences
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);    
require_once 'Classes/sx_db_ini.class.php';    

class TicketsPreferencesEditAction extends Action
{
    function execute (&$controller, &$request, &$user)
    {
        // make aliases
        $db =& sxDb::instance();
        
        if ($request->getParameter('save_prefs'))
        {
        	die('E:\Projects\Omni\ohd_new\modules\Manage\actions\TicketsPreferencesEditAction.class.php: 31');
        	
            // save prefs
            $prefs = $request->getParameter('prefs');
            $dbIni = new sxDbIni($db);
            $dbIni->saveIni(DB_PREF.'sys_options', $prefs);
            
            return VIEW_SUCCESS; 
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