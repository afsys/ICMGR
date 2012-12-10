<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to restore user`s pass.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Feb 07, 2006
 * @version    1.00 Beta
 */

require_once 'mail.class.php';
	
class RestorePassAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		$user_data = $request->getParameter('user_data');
		
		if (is_array($user_data)) {
			$db->q('SELECT * FROM #_PREF_users', array('user_email' => $user_data['user_email']));
			$res = $db->fetchAssoc();
			
			if (is_array($res)) {
				// send email
				$email_message = "THIS IS AN AUTO-GENERATED EMAIL FROM AQUEOUS TECHNOLOGIES' AUTOMATED HELP DESK. <br />".
				                 "PLEASE DO NOT REPLY TO THIS EMAIL. <br /><br />".
				                 "Login: {$res['user_login']} <br />".
				                 "Pass: {$res['user_pass']} <br />"
				;

				$mailer = new OhdMail();
				$email_subj = "OHD Password Retrieve";
				$mailer->SendEx($res['user_email'], $email_subj, $email_message);
				
				
				$res_message = 'Your password has been emailed to you: '. $res['user_email'] .'.';
			}
			else {
				$res_message = 'The e-mail address you specified does not exist.';
			}
		}
		
		if (!empty($res_message)) $request->setParameter('res_message', $res_message);
		
		$is_form_items_submit = $request->getParameter('is_form_items_submit');
		$ticket_product_id    = $request->getParameter('ticket_product_id');
		
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