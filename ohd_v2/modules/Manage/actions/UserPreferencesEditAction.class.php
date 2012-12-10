<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to manage user prefences.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 14, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);    
require_once 'Classes/sx_db_ini.class.php';    

class UserPreferencesEditAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		$dbIni = new sxDbIni($db);
		
		if ($request->getParameter('save_prefs')) {
			$user_id  = $user->getAttribute('user_id');

			$prefs = $request->getParameter('prefs');
			
			// get ex prefs
			$ex_prefs = $request->getParameter('ex_prefs');
			$user_variables = array();
			if (is_array($ex_prefs)) {
				foreach ($ex_prefs['user_variables_names'] as $k=>$name) {
					if (empty($name) || empty($ex_prefs['user_variables_values'][$k])) continue;
					$user_variables[$name] = $ex_prefs['user_variables_values'][$k];
				}
				$prefs['user_variables'] = $user_variables;
			}
			
			// save into DB
			$dbIni->removeGroup(DB_PREF.'users_options', 'user_variables');
			$dbIni->saveIni(DB_PREF.'users_options', $prefs, array('user_id' => $user_id));
			
			// cashing
			$user_options = $user->GetOptions();
			$user->setAttribute('user_options', $user_options);
			
			/* echo "<pre style='text-align: left'>";
			var_dump($ex_prefs);
			var_dump($prefs);
			var_dump($user_options);
			echo "</pre>"; /**/

			
			header('Location: index.php?module=Manage&action=UserPreferencesEdit');
			exit();
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