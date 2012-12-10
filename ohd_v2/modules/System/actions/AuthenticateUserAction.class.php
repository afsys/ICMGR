<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Action for authenticating user based on info passed from login form
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 4, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);

require_once 'Classes/users.class.php';
require_once 'Classes/groups.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/sx_db_ini.class.php';

class AuthenticateUserAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{  
		$db =& sxDb::instance();
		$username =  $request->getParameter('username');
		$email    =  $request->getParameter('email');
		$pass     =  $request->getParameter('password');
		$logout   =  $request->getParameter('logout') ? true : false;
		$dbIni    =  new sxDbIni($db);

		$errors = array();
		// already logged it... skip autentification
		if ($user->isAuthenticated() && !$logout) {
			$user->UpdateUserActivity();
			$controller->forward(SECURE_MODULE, SECURE_ACTION);
			return VIEW_NONE;
		}		
		// trying to login
		else if (!is_null($username) && !is_null($pass) && !$logout) {
			
			if (USE_KEY_VALIDATION)
			{
				// KEY VALIDATION
				include(OHD_LIB_DIR."/keyChecker.class.php");
				$kc = new keyChecker("http://www.omnihelpdesk.com/ohd_licenser/index.php");
	 			$check_result = $kc->checkKey($db->getOne("SELECT value FROM #_PREF_config_string WHERE name = 'license_key'"));
	 			if ($check_result !== true) {
					// error authenticating, return to login form
					$errors['login'] = addslashes($check_result);
					$request->setAttribute('errors', $errors);
					$request->setAttribute('username', $username);
					return VIEW_SUCCESS;
	 			}
			}


			// COMMON USER DATA
			$r = $db->q("SELECT * FROM #_PREF_users WHERE user_login = '$username' AND user_pass = '$pass'");
			$user_data = $db->fetchAssoc($r);
			
			if ($user_data) {
				
				if (preg_match('/\d{2,4}-\d{1,2}-\d{1,2}/', $user_data['disable_at'])) {
					if (strtotime ($user_data['disable_at']) >= time()) {
						// error authenticating, return to login form
						$errors['login'] = __('Your account have limited by ') . $user_data['disable_at'] . __(' date');
						$request->setAttribute('errors', $errors);
						$request->setAttribute('username', $username);
						return VIEW_SUCCESS;
					}
				}
				
				
				$user->setAttributeByRef('user_data', $user_data);
				$user->setAttribute('user_id', $user_data['user_id']);
				$user->setAttribute('lc_enabled', $user_data['lc_enabled']);
				
				// cashing system options
				$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
				$def_prefs = array (
					'common' => array (
						'language' => 'en-us'
					),
					'ticket_priorities' => array (),
					'ticket_types' => array (),
					'ticket_statuses' => array (),
					'tickets' => array (
						'status_for_reopened' => 'Open'
					),
					'tickets_list' => array (
						'flood_protection' => 0,
						'purge_opened'     => 0
					),
				);
				$sys_options = $dbIni->imposeArray($def_prefs, $sys_options);
				$user->setAttribute('sys_options', $sys_options);
				
				// user_options
				$user_options = $user->GetOptions();
				$user->setAttribute('user_options', $user_options);
				
				// set session livetime
				$_SESSION['session_lt_livetime'] = $user_options['defaults']['session_livetime'];

				// and setup some quick access attributes
				$user->setAttribute('username',    $username);
				$user->setAttribute('name',        $user_data['user_name']);
				$user->setAttribute('is_customer', $user_data['is_customer']);
				
				$user->setAttributeByRef('preferences', $user_data['preferences']);

				// set user rights
				$user_rights = $user_data['user_rights'];
				
				if ($user_data['is_sys_admin'] == 1) {
					global $SR_NOTES;
					foreach ($SR_NOTES as $k=>$v) $user_rights |= $k;
				}
				
				$user->setAttribute('user_rights', $user_rights);
				
				/* // PURGE TICKETS
				if (!empty($sys_options['tickets_list']['purge_opened']) && !$user->getAttribute('already_purged')) {
					$interval = $sys_options['tickets_list']['purge_opened'];
					
					$db->qI("#_PREF_tickets", array ('is_in_trash_folder' => 1), 'UPDATE',
						array (
							'#PLAIN' => "modified_at < (NOW() - INTERVAL ". $interval ." DAY)",
							'is_in_trash_folder' => 0
						)
					);
					
					$user->setAttribute('already_purged', 1);
				} */
				
				//set authentication status to true
				$user->setAuthenticated(true);
				$user->store();

				$user->UpdateUserActivity();
				$controller->forward(SECURE_MODULE, SECURE_ACTION); //forward to default secure page
				return VIEW_NONE;
			}
			// incorect login or pass
			else {
				if ($request->getParameter('is_userside')) {
					// redirect to user registration form
					header('Location: index.php?module=UserArea&action=Register');
					exit();
				}
				else {
					// error authenticating, return to login form
					$errors['login'] = __('Probably your username or password are invalid. Please try again.');
					$request->setAttribute('errors', $errors);
					$request->setAttribute('username', $username);
					return VIEW_SUCCESS;
				}
			}

		}
		// logout
		else {
			$this->logout($user);
		}

		// send them to the login form
		return VIEW_SUCCESS;
	}
	
	function logout(&$user) {
		$is_customer = $user->getAttribute('is_customer');
		
		$user->setAuthenticated(false);
		$user->clearAttributes();
		$user->clearPrivileges();

		if ($is_customer) {
			// redirect at user side
			header('Location: users/');
			exit();
		}
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

	function registerValidators (&$validatorManager, &$controller, &$request, &$user)
	{

	}    
}

?>