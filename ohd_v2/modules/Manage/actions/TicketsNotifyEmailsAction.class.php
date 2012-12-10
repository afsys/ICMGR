<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show tickets list
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 22, 2006
 * @version    1.00 Beta
 */
    
error_reporting(E_ALL);
require_once 'Classes/sx_db_ini.class.php';
    
class TicketsNotifyEmailsAction extends Action
{
    function execute (&$controller, &$request, &$user)
    {
        // make aliases
        $db =& sxDb::instance();
        $dbIni = new sxDbIni($db);
        
		$prefs = $request->getParameter('prefs');
		if (is_array($prefs))
		{
			$dbIni = new sxDbIni($db);
			if (count($prefs) > 0) $dbIni->saveIni(DB_PREF.'sys_options', $prefs);
			
			// recashing system options
			$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
			$user->setAttribute('sys_options', $sys_options);
		}


        /* echo "<pre>";
        var_dump($_POST);
        var_dump($errors);
        die('ddd');/**/
        
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