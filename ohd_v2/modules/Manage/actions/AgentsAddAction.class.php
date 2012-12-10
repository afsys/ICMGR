<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to add user
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */
	
error_reporting(E_ALL);

class AgentsAddAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// check rights
		$user_rights = $user->getAttribute('user_rights');
		$user_id     = $request->getParameter('user_id');
		
		// make aliases
		$db =& sxDb::instance();
		
		if ($request->getParameter('add_user'))
		{
			$message = array();
			

			// CHECK FOR ERRORS
			$username  = $request->getParameter('username');
			$password1 = $request->getParameter('password1');
			$password2 = $request->getParameter('password2');
			$firstName = $request->getParameter('firstName');
			$lastName  = $request->getParameter('lastName');
			$email     = $request->getParameter('email');
			$lc_priority = $request->getParameter('lc_priority');
			if (!$lc_priority) $lc_priority = 0;
		
			// error check our values
			// check submitted username
			if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $username))
			{
				$message[] = array ('message' => __('Please choose an alphanumeric username that consists of only letters and numbers.'));
			}
			
			if ($password1 != $password2)
			{
				$message[] = array ('message' => __('Passwords do not match.'));
			}
			
			// check groups
			$groups = ($request->getParameter('groups'));
			
			// get user`s rights
			$user_rights = 0;
			$user_rights_arr = $request->getParameter("user_rights");
			if (is_array($user_rights_arr)) {
				foreach ($user_rights_arr as $k=>$v) {
					$user_rights |= $v;
				}
			}
			
			$user_data = array (
				'user_login'    => $username,
				'user_name'     => $firstName,
				'user_lastname' => $lastName,
				'user_email'    => $email,
				'user_pass'     => $password1,
				'user_rights'   => $user_rights,
				'lc_priority'   => $lc_priority
			);
			
			// disable_at value
			$disable_at = $request->getParameter('disable_at');
			if (preg_match('/\d{2,4}-\d{1,2}-\d{1,2}/', $disable_at)) {
				$user_data['disable_at'] = $disable_at;
			}
			
			// no errors... add the user to the db
			if (count($message) == 0)
			{
				$user_id = $request->getParameter('user_id');
				if ($user_id)
				{
					if (empty($password1)) unset($user_data['user_pass']);
					$db->qI('#_PREF_users', $user_data, "UPDATE", array('user_id' => $user_id));
					$new_user_id = $user_id;
				}
				else
				{
					$new_user_id = $db->getNextId('#_PREF_users', 'user_id');
					$user_data['user_id'] = $new_user_id;
					$db->qI('#_PREF_users', $user_data);
				}
				
				// operate user groups
				$db->qD('#_PREF_users_groups', array ('user_id' => $new_user_id));
				if (is_array($groups))
				{
					foreach ($groups as $k=>$v)
					{
						// make group data
						$group_data = array (
							'group_id'    => $k, 
							'user_id'     => $new_user_id,
						);
						$db->qI('#_PREF_users_groups', $group_data);
					}
				}                

				if (!$user_id)
				{
					header('Location: index.php?module=Manage&action=AgentsList');
					exit;
				}
				
			}
		}
		
		
		if (isset($usergroups)) $request->setAttribute('usergroups', $usergroups);
		if (isset($user_data))  $request->setParameter('user_data',  $user_data);
		if (isset($message))    $request->setParameter('message', $message);
		
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