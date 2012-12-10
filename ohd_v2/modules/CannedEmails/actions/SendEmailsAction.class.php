<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to send canned emais messages
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 31, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);    
require_once 'Classes/sx_db_ini.class.php';    
require_once 'mail.class.php';
	
class SendEmailsAction extends Action
{
	// http://omni/ohd_new/index.php?module=CannedEmails&action=SendEmails&email=slyder@localhost.my,slyder@localhost.22&subj=subj&message=test&ticket_id=3&posted_by=2

	function execute (&$controller, &$request, &$user)
	{
		// java script header
		header("Content-type: text/javascript");
		
		// make aliases
		$db           =& sxDb::instance();
		$dbIni        =  new sxDbIni($db);
		$user_options =  $user->GetOptions();

		// get data
		$subj      = $request->getParameter('subj');
		$email     = $request->getParameter('email');
		$message   = $request->getParameter('message');
		$ticket_id = $request->getParameter('ticket_id');
		$posted_by = $request->getParameter('posted_by');

		if (empty($subj))      die("alert('subj' field is empty');");
		if (empty($email))     die("alert('email' field is empty');");
		if (empty($message))   die("alert('message' field is empty');");
		if (empty($ticket_id)) die("alert('ticket_id' field is empty');");
		if (empty($posted_by)) die("alert('posted_by' field is empty');");

		
		// extract emails list
		$errored = array();
		$emails  = array();
		$emails  = preg_split('/[,;]/', $email);
		
		// init mailer
		$mailer = new OhdMail();
		$mailer->AddVariables($user_options['user_variables']);
		
		foreach ($emails as $email)
		{
			$email = trim($email);
			
			$res = $mailer->SendEx($email, $subj, $message, array('allow_grouping' => 0));
			if (pear::isError($res)) {
				$errored[] = $email;
			}
		}
		
		/*echo "/ *";
		var_dump($prefs);
		echo "* /"; /**/
		
		if (count($errored) < count($emails)) 
		{
			$email_id = $db->getNextId('#_PREF_emails_history', 'email_id', array('ticket_id' => $ticket_id));
			
			$message_data = array(
				'ticket_id'  => $ticket_id,
				'email_id'   => $email_id,
				'posted_by'  => $posted_by,
				'email'      => $email,
				'subj'       => $subj,
				'message'    => $message,
				'rec_date'   => 'NOW()'
			);
			
			$db->qI('#_PREF_emails_history', $message_data);

			if (count($errored) > 0)
			{
				die('Could not send next E-Mails: '. join (", ", $errored));
			}
			else die('OK!');
		}
		else 
		{
			die('Could not send E-Mail!');
		}

		return VIEW_NONE;  
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_NONE;
	}

	function getPrivilege()
	{
		return null;
	}
	
	function isSecure()
	{
		return true;    
	}
}

?>