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

class PreferencesEditAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// check rights
		$user_rights = $user->getAttribute('user_rights');
		if (($user_rights & SR_CONF_SYS_PREFS) != SR_CONF_SYS_PREFS) {
			header('Location: index.php?module=System&action=HaveNoRights');
			exit();
		}
		
		// make aliases
		$db =& sxDb::instance();
		
		if ($request->getParameter('save_prefs')) {
			$prefs = $request->getParameter('prefs');
			if (!empty($prefs['tickets_list']['flags']))         $prefs['tickets_list']['flags']         = trim($prefs['tickets_list']['flags']);
			if (!empty($prefs['tickets_list']['banned_emails'])) $prefs['tickets_list']['banned_emails'] = trim($prefs['tickets_list']['banned_emails']);
			if (!empty($prefs['tickets_list']['banned_ips']))    $prefs['tickets_list']['banned_ips']   = trim($prefs['tickets_list']['banned_ips']);

			$dbIni = new sxDbIni();
			if (count($prefs) > 0) $dbIni->saveIni(DB_PREF.'sys_options', $prefs);
			
			// recashing system options
			$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
			$user->setAttribute('sys_options', $sys_options);
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