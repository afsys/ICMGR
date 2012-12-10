<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to register user.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Feb 07, 2006
 * @version    1.00 Beta
 */

require_once 'Classes/users.class.php';
require_once 'Classes/groups.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/sx_db_ini.class.php';

class RegisterAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{  
		$db =& sxDb::instance();
		$username =  $request->getParameter('username');
		$email    =  $request->getParameter('email');
		$pass     =  $request->getParameter('password');
		$logout   =  $request->getParameter('logout') ? true : false;
		$dbIni    =  new sxDbIni($db);
		// make aliases
		//$db =& sxDb::instance();
		//$sys_options = $user->getAttribute('sys_options');
		
		$user_data = $request->getParameter('user_data');
		if (is_array($user_data)) {
			__('');
			// check items
			$message = array();
			if (empty($user_data['user_login'])) $message[] = array ('message' => __('User login could not be empty'));
			if (empty($user_data['user_pass']))  $message[] = array ('message' => __('User pass could not be empty'));
			if (empty($user_data['user_email'])) $message[] = array ('message' => __('User email could not be empty'));
			if ($user_data['user_pass'] != $user_data['user_pass2']) $message[] = array ('message' => 'Passwords do not equal');
			
			if (count($message) > 0) {
				$request->setParameter('message', $message);
				$request->setParameter('user_data', $user_data);
			}
			// add user
			else {
				$user_data['user_id'] = $db->getNextId('#_PREF_users', 'user_id');
				$user_data['register_code'] = md5(time());
				$user_data['user_enabled']  = 0;
				$user_data['is_customer']   = 1;
				unset($user_data['user_pass2']);
				
				$db->qI('#_PREF_users', $user_data);
				
				/*
				// send email
				$ticket_url = "{$sys_options['common']['ticket_form_url']}?ticket_id={$ticket_id}&email={$ticket->data['customer_email']}&button=View+ticket";
				$email_body = $sys_options['emails_templates']['ticket_new_message'];
				$email_body = str_replace('{$ticket_url}',  $ticket_url, $email_body);
				$email_body = str_replace('{$ticket_text}', $message_data['message_text'], $email_body);
				$email_message = "THIS IS AN AUTO-GENERATED EMAIL FROM AQUEOUS TECHNOLOGIES' AUTOMATED HELP DESK. <br />".
								 "PLEASE DO NOT REPLY TO THIS EMAIL.";
				//$email_body;

				$mailer = new OhdMail();
				$email_subj = "Ticket Num. {$ticket->data['ticket_num']} - '{$ticket->data['caption']}' new message notification from customer";
				$from_name = $sys_options['common']['company_name']." ticket #{$ticket->data['ticket_num']}";
				$mailer->SendEx($user_data['user_email'], $email_subj, "", $email_message, null, $from_name);
				
				
				echo "<pre>";
				var_dump($user_data);
				echo "</pre>";
				
				die('aaa');
				*/
				
				$request->setParameter('thanks', $user_data);
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