<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to edit ticket.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 5, 2005
 * @version    1.00 Beta
 */

require_once 'Classes/tickets.class.php';
require_once 'Classes/sx_db_ini.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/users.class.php';
require_once 'send_file.function.php';
require_once 'mail.class.php';

class TicketsEditAction extends Action
{
	var $is_error = false;
	
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db            =& sxDb::instance();
		$dbIni         =  new sxDbIni($db);
		$tickets       =  new Tickets($db);
		$user_options  =  $user->GetOptions();
		$this->request =& $request;

		// check if it is unauthenticated user
		if (!$user->isAuthenticated()) {
			$_num   = (int) $request->getParameter('ticket_num');
			$_email = $request->getParameter('customer_email');
			
			$ticket_id = $db->getOne('SELECT * FROM #_PREF_tickets tickets', array('ticket_num' => $_num, 'customer_email' => $_email));
			
			// incorect user data
			if (!is_numeric($ticket_id)) {
				header('Location: index.php');
				exit();
			}
			
			$_GET['ticket_num']     = $_num;
			$_GET['customer_email'] = $_email;
			$request->setParameter('ticket_id', $ticket_id);
		}
		
        //$sys_options = $user->getAttribute('sys_options'); 
        $sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
		$submit_type = $request->getParameter('submit_type');
		$user_id     = $user->getAttribute('user_id');
		$ticket_id   = $request->getParameter('ticket_id');
		
		$ticket_atachment = $request->getParameter('ticket_atachment');
		
		
		// CHECK RIGHT FOR VIEW CURRENT TICKET
		$ticket_where = $user->GetTicketsRightsLimitClause();
		$ticket_where['OR'] = array('ticket_id' => $ticket_id);
		
		$cnt = $db->getOne('SELECT COUNT(*) FROM #_PREF_tickets tickets', $ticket_where);
		
		if ($cnt == 0) {
			return VIEW_NORIGTHS;  
		}
		
		// UPDATE LAST TICKET VIEW DATE
		if ($user_id) {
			$ut_ids = array (
				'user_id'   => $user_id,
				'ticket_id' => $ticket_id
			);
			$cnt = $db->getOne('SELECT COUNT(*) FROM #_PREF_users_tickets_props', $ut_ids);
			if ($cnt) {
				$db->qI('#_PREF_users_tickets_props', array ('last_view_time' => 'NOW()'), 'UPDATE', $ut_ids);
			}
			else {
				$ut_ids['last_view_time'] = 'NOW()';
				if ($user_id && $ticket_id) $db->qI('#_PREF_users_tickets_props', $ut_ids);
			}
		}
		
		
		// FLOOD PROTECTION MESSAGE
		if ($sys_options['tickets_list']['flood_protection'] && @$_SESSION['ticket_just_added'] !== 1) {
			$cnt = $db->getOne(
				"SELECT COUNT(*) FROM #_PREF_tickets_messages", 
				array (
					'ticket_id' => $ticket_id,  
					'message_creator_user_id' => $user_id,
					'#PLAIN' => "message_datetime > (NOW() - INTERVAL ". $sys_options['tickets_list']['flood_protection'] ." SECOND)"
			));
			
			if ($cnt > 0) {
				$request->setParameter(
					'error_message', 
					array (
						'caption' => 'Flood protection system: ',
						'message' => 'please wait for '.$sys_options['tickets_list']['flood_protection'].' seconds')
					);
				return VIEW_SUCCESS; 
			}
		}
		
		// SHOW TICKET ATACHMENT
		if ($ticket_atachment) {
			if (!is_dir($sys_options['attachments']['directory'])) return VIEW_SUCCESS; 
			$upload_dir = str_replace('\\', '/', realpath($sys_options['attachments']['directory']).'/');
			
			$ticket_id = $request->getParameter('ticket_id');
			$db->q(
				"SELECT message_atachment_file, message_atachment_name FROM #_PREF_tickets_messages", 
				array('ticket_id' => $ticket_id, 'message_id' => $ticket_atachment)
			);
			list ($filepath, $filename) = $db->fetchArray();

			require_once 'PEAR/PEAR.php';
			require_once 'PEAR/HTTP/Download.php';
			$params = array (
				'file' => $upload_dir.$filepath,
				'contentdisposition' => array(HTTP_DOWNLOAD_ATTACHMENT, $filename),
				'gzip' => false,
				'cache' => false
			);
			ob_end_clean();
			header("Content-Encoding: none");
			$res = HTTP_Download::staticSend($params);
			//die($upload_dir.$filepath);

		
			//SendFile($upload_dir.$filepath, $filename);
			//echo "$upload_dir$filepath - $filename";
			die();
		}
		
		// ADD NEW TICKET
		if ($submit_type && $ticket_id)
		{
			// email customer if needed
			$ticket = new Ticket($ticket_id);
			
			$message_data = array (
				'ticket_id' => $ticket_id
			);
			
			// ATACHMENT PROCESSING
			/*echo "<PRE>";
			var_dump($_FILES);*/
			//var_dump($sys_options['attachments']['directory']);
			
			switch ($submit_type)
			{
				case 'message':
					$caption = 'file_atachment';
					break;
				case 'note':
					$caption = 'file_atachment_';
					break;
					
				case 'save_ex_options':
					// @TODO: make saving thought AJAX
					
					$ex_options = $request->getParameter('ticket_ex_options');
					
					// update ticket ex_options
					$ticket_where = array ('ticket_id' => $ticket_id);
					$db->qI('#_PREF_tickets', $ex_options, 'UPDATE', $ticket_where);
					
					if ($user->isAuthenticated()) {
						header("Location: index.php?module=Tickets&action=TicketsEdit&ticket_id=$ticket_id");
						exit();
					}
					
					break;
					
				case 'save_ex_notes':
					// @TODO: make saving thought AJAX
					$ticket->setAddOptions('ex_notes', $request->getParameter('ex_notes'));
					header("Location: index.php?module=Tickets&action=TicketsEdit&ticket_id=$ticket_id");
					exit();
					
			}
			
			if (!empty($_FILES[$caption]['name']))
			{
				$res = $this->processAtachment($caption, $sys_options['attachments']['directory']);
				if (is_array($res))
				{
					$message_data['message_atachment_file'] = $res['filenamepath'];
					$message_data['message_atachment_name'] = $res['filename'];
				}
				else
				{
					$this->is_error = true;
					return VIEW_SUCCESS; 
				}
			}
			
			// convert plain text into HTML
			$message_text = $request->getParameter('new_'.$submit_type);
			if (!is_html($message_text)) $message_text = nl2br($message_text);
			
			
			$message_id = $db->getNextId('#_PREF_tickets_messages', 'message_id');
			$message_data['message_creator_user_id']   = $user_id;
			$message_data['message_creator_user_name'] = $user->isAuthenticated() ? '' : $_email;
			$message_data['message_creator_user_ip']   = $_SERVER['REMOTE_ADDR'];
			$message_data['message_id']                = $message_id;
			$message_data['message_text']              = $message_text;
			$message_data['message_text_is_html']      = 1;
			$message_data['message_datetime']          = 'NOW()';
			$message_data['message_type']              = $submit_type;
			//if ($submit_type == 'message') $message_data['last_message_posted_by_admin'] = 1;
			
			// add KB articles
			if (preg_match_all('/{KB:(\d+)}/', $message_data['message_text'], $match)) {
				foreach ($match[1] as $kbitem_id) {
					$text = $db->getOne("SELECT item_notes FROM #_PREF_kb_items", array('item_id' => $kbitem_id));
					if ($text) {
						$message_data['message_text'] = str_replace("{KB:$kbitem_id}", $text, $message_data['message_text']);
					}
				}
			}
			
			
			if ($message_data['message_text'] || $message_data['message_atachment_file']) {
				$db->qI('#_PREF_tickets_messages', $message_data);
				$db->qI(
					'#_PREF_tickets tickets', 
					array(
						'modified_at' => 'NOW()',
						'is_in_trash_folder' => 0
					), 
					'UPDATE', array('ticket_id' => $ticket_id));
				
				if ($ticket->data['status'] == $sys_options['tickets']['status_for_closed']) {
					$ticket->Open($user_id, $sys_options['tickets']['status_for_new']);
				}
			}
			
			if ($submit_type == 'message') $ticket->UpdateLastMessagePostedByAdmin();
			

			$is_authenticated = $user->isAuthenticated();
			

			// get caption
			$message_caption = $ticket->data['caption'];
			if (empty($message_caption)) {
				$message_caption = substr(preg_replace('/\s+/si', ' ', strip_tags($ticket->data['description'])), 0, 22);
			}
			
			// prepare mailer
			if ($is_authenticated) {
				// notify customer about new ticket
				if ($ticket->data['ticket_email_customer'] && $message_data['message_type'] != 'note') {
					$vars = $user_options['user_variables'];
					$vars['ticket_message'] = nl2br($message_data['message_text']);
					$ticket->SendNotifyEmail('ticket_new_message', null, $vars);
				}
			}
			else {
				$ticket_data = $tickets->GetItemData($ticket_id);
				
				$assigned_to = $ticket->data['assigned_to'];
				if (!empty($assigned_to)) {
					$users = new Users($db);
					$user_data = $users->getUserData($assigned_to);
					
					// notify operator about new ticket
					if ($user_data['user_email'] && $message_data['message_type'] != 'note') {
						$mailer = new OhdMail();
						$mailer->AddVariables($user_options['user_variables']);
						$mailer->AddVariables(
							array (
								'ticket_num'     => $ticket->data['ticket_num'],
								'ticket_caption' => $ticket->data['caption'],
								'ticket_url'     => "{$sys_options['common']['ticket_form_url']}?ticket_id={$ticket->data['ticket_num']}&email={$ticket->data['customer_email']}&button=View+ticket",
								'ticket_message' => nl2br($message_data['message_text']), 
								
							)
						);
						
						$email_message = "THIS IS AN AUTO-GENERATED EMAIL FROM AQUEOUS TECHNOLOGIES' AUTOMATED HELP DESK. <br />".
										 "PLEASE DO NOT REPLY TO THIS EMAIL.";
						$email_subj = "Ticket Num. {$ticket->data['ticket_num']} - '{$message_caption}' new customer message notification";
						$from_name = $sys_options['common']['company_name']." ticket #{$ticket->data['ticket_num']}";
						$mailer->SendEx($user_data['user_email'], $email_subj, $email_message, array('from' => $from_name));
					}
				}
			}
			
			// flood protection shield
			$_SESSION['ticket_just_added'] = 1;
			$request->setParameter('ticket_id', $ticket_id);
			
			if ($user->isAuthenticated()) {
				header("Location: index.php?module=Tickets&action=TicketsEdit&ticket_id=$ticket_id");
				exit();
			}
		}

		$_SESSION['ticket_just_added'] = 0;
		return VIEW_SUCCESS;  
	}
	
	function processAtachment($atachment_name, $upload_dir, $allowed_extensions = array())
	{
		if (!is_dir($upload_dir))
		{
			$this->request->setAttribute('message', 'Could not find directory for uploading files. Please configure it at \'Configure -> System Options\'');
			return null;
		}
		
		$upload_dir = str_replace('\\', '/', realpath($upload_dir).'/');
		
		//check that file upload was successful
		$error_code = $_FILES[$atachment_name]['error'];
		if ($error_code == 1 || $error_code == 2)
		{
			$request->setAttribute('message', 'Sorry, your attachment is too large.');
		}

		else if ($error_code == 3)
		{
			$request->setAttribute('message', 'There was an error receiving your file attachment -- please try again.');
		}

		else if ($error_code == 0)
		{
			$file = true;

			//we have an attachment to add too...
			$unique_name = md5($_FILES[$atachment_name]['name'].microtime());
			$attachment_name = basename($_FILES[$atachment_name]['name']);

			// TODO: check allowed extensions
			$allow_file = true; //false;

			
			if ($allow_file)
			{
				$tmp_file = $_FILES[$atachment_name]['tmp_name'];
				$new_file = $upload_dir.$unique_name;

				if (@move_uploaded_file($tmp_file, $new_file) == false)
				{
					$this->request->setAttribute('message', 'There was an error storing your attachment -- please try again (try to change access rights)');
				}
				else
				{
					//$query_values = array('filepath' => $unique_name, 'filename' => $attachment_name, 'tid' => $ticketID, 'from_cust' => 0);
					//$db->autoExecute('ohd_attachments', $query_values, DB_AUTOQUERY_INSERT);
					$res = array (
						'filenamepath' => $unique_name,
						'filename'     => $attachment_name
					);
					return $res;
				}
			}

			else
			{
				$this->request->setAttribute('message', 'There was an error storing your attachment -- The file type was rejected for security reasons.');
			}
		}
		return null;
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
		return null;
	}
	
	function isSecure()
	{
		return false;    
	}
}

?>