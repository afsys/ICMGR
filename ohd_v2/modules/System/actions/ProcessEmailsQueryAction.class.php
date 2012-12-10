<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action neccesary for operation with emails query.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Mar 9, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'mail.class.php';
require_once 'Classes/sx_db_ini.class.php';
	
class ProcessEmailsQueryAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		$dbIni       = new sxDbIni($db);
		$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
		
		
		$verbose = $request->getParameter('verbose');
		
		
		// test message
		$sender = new OhdMail();
		//$sender->SendEx('Slyder <slyder@homa.net.ua>, Kon Local [slyder12-asd@localhost.me], my@email.com', 'subj', 'body'); /**/
		
		
		
		// SEND GROUP MESSAGES
		// get new messages
		$db->q('SELECT * FROM #_PREF_tickets_emails_query', array('is_sended' => 0, 'allow_grouping' => 1));
		
		$messages_by_users = array();
		while ($message = $db->fetchAssoc()) {
			$email_to = $message['email_to'];
			if (empty($messages_by_users[$email_to])) $messages_by_users[$email_to] = array();
			$messages_by_users[$email_to][] = $message;
		}

		//echo "<pre>";
		//var_dump($messages_by_users);

		
		// prepare message and send
		foreach ($messages_by_users as $email_to=>$emails) {
			$message = "";
			foreach ($emails as $email_data) {
				$message .= "<div style=\"padding: 5px 0;\">{$email_data['message']}</div>";;
			}
			
			if (!empty($sys_options['emails_templates']['ticket_group_header'])) {
				$message = "<div style=\"padding-bottom: 10px;\">{$sys_options['emails_templates']['ticket_group_header']}</div>".$message;
			}
			if (!empty($sys_options['emails_templates']['ticket_group_footer'])) {
				$message .= "<div style=\"padding-top: 10px;\">{$sys_options['emails_templates']['ticket_group_footer']}</div>";
			}
			
			$subj = !empty($sys_options['emails_templates']['ticket_group_caption']) ? $sys_options['emails_templates']['ticket_group_caption'] : 'OHD: Events happened from {date_from}';
			$subj = str_replace('{date_from}', date("F j, Y, g:i a"), $subj);
			
			//$res = $sender->SendSimple($email_to, $subj, $message);
			$res = true;
			$cnt = count($emails);
			echo "<div><em style=\"font-weight: bold;\">$email_to</em> - sending $cnt messages - <em>".($res ? 'ok' : 'error')."</em>";
			if ($verbose) echo "<p>$message</p>";
			echo "</div>";
			
			$db->qI('#_PREF_tickets_emails_query', 
					array('is_sended' => 1, 'sent_at' => 'NOW()'), 
					'UPDATE', 
					array('is_sended' => 0, 'allow_grouping' => 1, 'email_to' => $email_to)
			);
		}
		
		
		// SEND SINGLE MESSAGES
		// get new messages
		$db->q('SELECT * FROM #_PREF_tickets_emails_query', array('is_sended' => 0, 'allow_grouping' => 0));
		$ids = array();
		while ($message = $db->fetchAssoc()) {
			//$res = $sender->SendSimple($message['send_to'], $message['subject'], $message['message']);
			$ids[] = $message['email_id'];
		}
		
		if (count($ids) > 0) {
			$db->qI('#_PREF_tickets_emails_query', 
				array('is_sended' => 1, 'sent_at' => 'NOW()'), 
				'UPDATE', 
				array('is_sended' => 0, 'allow_grouping' => 0, 'email_id' => $ids));
		}
		return VIEW_NONE;  
	}
	
	function isSecure()
	{
		return false;    
	}
}

?>