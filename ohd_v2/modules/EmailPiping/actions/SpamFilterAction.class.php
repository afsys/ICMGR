<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to view filters list.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Mar 22, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/sx_db_ini.class.php'; 

class SpamFilterAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		
		$prefs = $request->getParameter('prefs');
		
		if ($prefs)
		{
			$dbIni = new sxDbIni($db);
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
		$controller->forward(ERROR_404_MODULE, ERROR_404_ACTION);
		return VIEW_NONE;
	}

	function getPrivilege()
	{
		return null;
	}

	function isSecure()
	{
	
	}

}
?>