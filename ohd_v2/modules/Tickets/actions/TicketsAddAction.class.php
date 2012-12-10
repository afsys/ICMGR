<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to add new ticket.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'mail.class.php';
require_once 'Classes/ticket.class.php';
require_once 'Classes/sx_db_ini.class.php';
	
class TicketsAddAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// is_authenticated
		$is_authenticated = $user->isAuthenticated();
		
		// make aliases
		$db                =& sxDb::instance();
		$dbIni             =  new sxDbIni($db);
		$sys_options       =  $dbIni->LoadIni(DB_PREF.'sys_options');
		$user_options      =  $user->GetOptions();
		$this->sys_options = $sys_options;
		$this->request     =& $request;
		
		$is_form_items_submit = $request->getParameter('is_form_items_submit');
		$ticket_product_id    = $request->getParameter('ticket_product_id');
		
		if ($is_form_items_submit && $ticket_product_id) {
			// get ticket data
			$user_id = $user->getAttribute('user_id');
			
			// flood protection
			if ($sys_options['tickets_list']['flood_protection'])
			{
				$ticket_params = array (
						'creator_user_id' => $user_id,  
						'#PLAIN' => "created_at > (NOW() - INTERVAL ". $sys_options['tickets_list']['flood_protection'] ." SECOND)"
				);
				if ($request->getParameter('ticket_id')) $ticket_params['ticket_id'] = array('!=', $request->getParameter('ticket_id'));
				$cnt = $db->getOne("SELECT COUNT(*) FROM #_PREF_tickets", $ticket_params);
			
				if ($cnt > 0) {
					$request->setParameter(
						'error_message', 
						array (
							'caption' => 'Flood protection system: ',
							'message' => 'please wait for '.$sys_options['tickets_list']['flood_protection'].' seconds'
					));
					return VIEW_SUCCESS; 
				}
			}
			
			// convert plain text into HTML
			$ticket_description = $request->getParameter('ticket_description');
			if (!is_html($ticket_description)) $ticket_description = nl2br($ticket_description);
			
			$ticket_data = array (
				'group_id'              => $request->getParameter('group_id'),
				'ticket_product_id'     => $ticket_product_id,
				'ticket_product_ver'    => $request->getParameter('ticket_product_ver'),
				'caption'               => $request->getParameter('ticket_caption'),
				'description'           => $ticket_description,
				'description_is_html'   => 1,
				'customer_name'         => $request->getParameter('ticket_customer_name'),
				'customer_phone'        => $request->getParameter('ticket_customer_phone'),
				'customer_email'        => $request->getParameter('ticket_customer_email'),
				'customer_add_emails'   => $request->getParameter('ticket_customer_add_emails'),
				'status'                => $request->getParameter('ticket_status'),
				'priority'              => $request->getParameter('ticket_priority'),
				'type'                  => $request->getParameter('ticket_type'),
				'expired_at'            => $request->getParameter('expired_at') ? $request->getParameter('expired_at') : null,
				'assigned_to'           => $request->getParameter('assigned_to'),
				'due_date'              => $request->getParameter('due_date'),
				'ticket_email_customer' => $request->getParameter('ticket_email_customer') ? 1 : 0,
				'modified_at'           => 'NOW()',
				'created_at'            => 'NOW()'
			); 
			
			
			// get primary email - only first
			if (preg_match_all('/(\b[\w\s]*)([<\[]|)\b([-a-z0-9_.]+@[-a-z0-9_.]+)([>\]]|)/', $request->getParameter('ticket_customer_email'), $match)) {
				$ticket_data['customer_email'] = $match[0][0];
			}
			
			// get additional emails
			if (preg_match_all('/(\b[\w\s]*)([<\[]|)\b([-a-z0-9_.]+@[-a-z0-9_.]+)([>\]]|)/', $request->getParameter('ticket_customer_add_emails'), $match)) {
				$ticket_data['customer_add_emails'] = implode(', ', $match[0]);
				
			}
			
			// update ticket
			if ($ticket_id = $request->getParameter('ticket_id')) {
				if (defined('F_MANUAL_TICKET_NUM')) {
					$ticket_num = $request->getParameter('ticket_num');
					if ($ticket_num) {
						$ticket = new Ticket($ticket_id);
						$ticket_data['ticket_num'] = $ticket_num;
						
						// send rename ticket num notify email
						if ($ticket->data['ticket_num'] != $ticket_num) {
							$ticket->data['ticket_num'] = $ticket_num;
							$ticket->data['caption']    = $request->getParameter('ticket_caption');
							$ticket->SendNotifyEmail('ticket_renamed_by_advisor', null, $user_options['user_variables']);
						}
					}
				}
				
				$ticket_where = array ('ticket_id' => $ticket_id);
				$ticket_data['ticket_id'] = $ticket_id;
				$db->qI('#_PREF_tickets', $ticket_data, 'UPDATE', $ticket_where);
				$history_notes = 'Updated';
			}
			// insert ticket
			else {
				// set ticket number
				$ticket_num = $request->getParameter('ticket_num');
				if (defined('F_EXPIREDAT_FLD') && $ticket_num) {
					// TODO: а че тут надо-то было? хм
				}
				else {
					$ticket_num = Tickets::GenerateUniqId();
				}
				
				// insert ticket
				$ticket_id = $db->getNextId('#_PREF_tickets', 'ticket_id');
				$ticket_data['ticket_id']     = $ticket_id;
				$ticket_data['ticket_num']    = $ticket_num;
				$ticket_data['creator_user_id'] = $user_id == null ? 0 : $user_id;;
				
				$db->qI('#_PREF_tickets', $ticket_data);
				$history_notes = 'Created';
				
				
				// NOTIFICATION EMAIL
				$ticket = new Ticket($ticket_id);
				// to customer
				if (!defined('F_MANUAL_TICKET_NUM')) {
					$ticket->SendNotifyEmail('ticket_created', null, $user_options['user_variables']);
				}
				
				// to recipient
				$assigned_to = $request->getParameter('assigned_to');
				if (!empty($assigned_to)) {
					$recipient_email = $db->getOne('SELECT user_email FROM #_PREF_users', array('user_id' => $assigned_to));
					if (!empty($recipient_email)) {
						$mailer = new OhdMail();
						$mailer->AddVariables($user_options['user_variables']);
						$mailer->AddVariables(
							array (
								'ticket_num'     => $ticket_num,
								'ticket_caption' => $request->getParameter('ticket_caption'),
								'ticket_url'     => "{$sys_options['common']['ticket_form_url']}?ticket_id={$ticket->data['ticket_num']}&email={$ticket->data['customer_email']}&button=View+ticket",
							)
						);
						
						$email_subj    = "Ticket Num. $ticket_num - '".$request->getParameter('ticket_caption')."' has been created";
						$email_message = "THIS IS AN AUTO-GENERATED EMAIL FROM AQUEOUS TECHNOLOGIES' AUTOMATED HELP DESK.PLEASE DO NOT REPLY TO THIS EMAIL. <br><br>New ticket num. $ticket_num created and assigned to you !";
						
						$res = $mailer->SendEx($recipient_email, $email_subj, $email_message);
						$send_result = !pear::isError($res);
					}
				}
			}
			
			// ticket history
			$history_data = array(
				'ticket_id' => $ticket_id,
				'user_id'   => $user_id,
				'his_notes' => $history_notes,
				'rec_date'  => 'NOW()'
			);
			$db->qI('#_PREF_tickets_history', $history_data);

			// add fields
			$field_data = array (
				'ticket_id'         => $ticket_id,
				'ticket_product_id' => $ticket_product_id
			);
			
			$db->qD('#_PREF_tickets_products_forms_values', $field_data);
			
			$form_fields = $request->getParameter('form_field');
			if (is_array($form_fields)) {
				foreach ($form_fields as $field_id=>$field_value) {
					$field_data['ticket_field_id']    = $field_id;
					$field_data['ticket_field_value'] = $field_value;
					
					$db->qI('#_PREF_tickets_products_forms_values', $field_data);
				}
			}
			
			// process attachemets
			if (!empty($_FILES['file_atachment']['name']))
			{
				$res = $this->processAtachment('file_atachment', $sys_options['attachments']['directory']);
				if (is_array($res)) {
					$ticket = new Ticket($ticket_id);
					$ticket->AddMessage('', '', $res);
				}
				else {
//					$this->is_error = true;
//					return VIEW_SUCCESS; 
				}
			}
			
			if ($is_authenticated) {
				//if ($ticket_id = $request->getParameter('ticket_id')) 
				if (!empty($ticket_id)) header('Location: index.php?module=Tickets&action=TicketsEdit&ticket_id='.$ticket_id);
				else header('Location: index.php?module=Tickets&action=TicketsList&set_filter=1&filter[special]=opened');
			}
			else {
				//header('Location: index.php?module=UserArea&action=Index');
				$request->setParameter('ticket_num', $ticket_num);
				$request->setParameter('customer_email', $request->getParameter('ticket_customer_email'));
				$controller->forward('Tickets', 'TicketsEdit');
				//$controller->forward('UserArea', 'TicketAddThanks');
			}
			
			
			die();
		}
		// var_dump($sys_options);
		
		
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
			$unique_name     = md5($_FILES[$atachment_name]['name'].microtime());
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