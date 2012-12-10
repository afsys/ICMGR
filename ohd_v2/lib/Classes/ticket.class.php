<?php

require_once 'users.class.php';
require_once 'tickets.class.php';
require_once 'sx_db_ini.class.php';
require_once 'mail.class.php';

/**
 * Tickets list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

class Ticket
{
	/**
	 * Database object
	 * @var sxDB
	 */
	var $db = null;
	
	/**
	 * Data ticket_id
	 * @var array
	 */
	var $data = null;
	
	/**
	 * Systems options cashe.
	 * @var array
	 */
	var $sys_options = null;
	
	function Ticket($ticket_data)
	{
		$this->db =& sxDB::instance();
		// ticket_id
		if (is_numeric($ticket_data)) {
			$tickets    = new Tickets($db);
			$this->data = $tickets->GetItemData($ticket_data);
		}
		// ticket_data
		else {
			$this->data = $data;
		}
	}
	
	function AddHistoryNote($user_id, $note, $note_date = 'NOW()')
	{
		// add ticket history note
		$history_data = array(
			'ticket_id' => $this->data['ticket_id'],
			'user_id'   => $user_id,
			'his_notes' => $note,
			'rec_date'  => $note_date
		);
		
		// insert history data
		$this->db->qI('#_PREF_tickets_history', $history_data);
		
	}
	
	
	/**
	 * Add new message in ticket archive.
	 * @param     string   $msg_type          message type enum ('message', 'note', 'ticket')
	 * @param     string   $msg_text          message text or html
	 * @param     string   $msg_attachment    array ('filenamepath' => ..., 'filename' => ....)
	 * @param     array    $other_params      array of auxilary message parameters
	 * @return    boolean   true if message added, else false
	 */
	function AddMessage($msg_type, $msg_text, $msg_attachment = null, $other_params = null)
	{
		if (!is_numeric($this->data['ticket_id'])) die('E:\Projects\Omni\ohd_new\lib\Classes\ticket.class.php: 77');
		$message_data = array();
		
		$message_id = $this->db->getNextId('#_PREF_tickets_messages', 'message_id');
		$message_data['ticket_id']                    = $this->data['ticket_id'];
//		$message_data['message_creator_user_id']      = $this->data['user_id'];
		$message_data['message_creator_user_ip']      = $_SERVER['REMOTE_ADDR'];
		$message_data['message_id']                   = $message_id;
		$message_data['message_datetime']             = 'NOW()';
		$message_data['message_type']                 = $msg_type;
		
		// check txt or html
		$message_data['message_text'] = $msg_text;
		$message_data['message_text_is_html'] = is_html($msg_text) ? 1 : 0;
		
		// attachment
		if (is_array($msg_attachment)) {
			$message_data['message_atachment_file'] = $msg_attachment['filenamepath'];
			$message_data['message_atachment_name'] = $msg_attachment['filename'];
		}
		
		// other_params
		if (is_array($other_params)) {
			foreach ($other_params as $k=>$v) $message_data[$k] = $v;
		}
		
		//if ($message_data['message_text'] || $message_data['message_atachment_file'])
		{
			$this->db->qI('#_PREF_tickets_messages', $message_data);
			
			if (empty($message_data['message_creator_user_id']) || !is_numeric($message_data['message_creator_user_id'])) {
				$message_data['message_creator_user_id'] = 0;
			}

			$this->SetUpdatedNow($message_data['message_creator_user_id']);

			if ($message_data['message_creator_user_id'] == 0) {
				$this->UpdateProperty($message_data['message_creator_user_id'], 'status', $this->GetUnansweredStatus());
			}
			
			if ($msg_type == 'ticket') $this->AddHistoryNote($message_data['message_creator_user_id'], 'Ticket merged...');
			else $this->AddHistoryNote($message_data['message_creator_user_id'], 'Posted new message');
			
			// send notification email
			
		}
		
		return $message_id;
	}

	
	
	/**
	 * Move all child ticket messages to current ticket and make message from child ticket description.
	 * @param     integer   $child_ticket_id       ticket_id number for adding to current ticket
	 * @return    boolean   true if ticket appended, else false
	 */
	function AppendTicket($child_ticket_id)
	{
		if (!is_numeric($child_ticket_id)) return false;
		if (!is_numeric($this->data['ticket_id'])) return false;
		if ($this->data['ticket_id'] == $child_ticket_id) return false;
		
		$child_ticket = new Ticket($child_ticket_id);
		$this->AddMessage('ticket', $child_ticket->data['description'], null, array('message_subject' => $child_ticket->data['caption']));
		
		$r = $this->db->q_('SELECT * FROM #_PREF_tickets_messages', array('ticket_id' => $child_ticket_id));
		while ($msg = $this->db->fetchAssoc($r)) {
			$msg['ticket_id'] = $this->data['ticket_id'];
			unset($msg['message_id']);
			$this->db->qI('#_PREF_tickets_messages', $msg);
		}
		
		$child_ticket->TrashIt($user_id); 
		return true;
	}
	
	/* function AutoArchiveTickets()
	{
		$db =& sxDB::instance();
		$sys_options
		
		if (defined('F_TIME_ARCHIVE_TICKETS') && !empty($sys_options['auto_ticket_arhciving']['archive_after_days']) &&
		    is_numeric($sys_options['auto_ticket_arhciving']['archive_after_days']) && $sys_options['auto_ticket_arhciving']['archive_after_days'] > 0) {
			$status = $sys_options['auto_ticket_arhciving']['status_for_ticket'] ? $sys_options['auto_ticket_arhciving']['status_for_ticket'] : 'Archived';
				
			$db->q('
				UPDATE #_PREF_tickets
				SET
				   is_in_trash_folder = 1,
				   ticket_num = CONCAT(\''. $sys_options['auto_ticket_arhciving']['prefix'] .'\', ticket_num),
				   status = \''. $status .'\'
				WHERE 
				   is_in_trash_folder != 1
				   AND closed_at IS NOT NULL
				   AND closed_at < (NOW() - INTERVAL '. $sys_options['auto_ticket_arhciving']['archive_after_days'] .' DAY)
			');
		}
	} */

	
	/**
	 * Set ticket closed_at date and set close status (unique for system)
	 */
	function Close($user_id, $closed_status)
	{
		$this->UpdateProperty($user_id, 'status', $closed_status);
	}
	
	/**
	 * Removes ticket from database.
	 */
	function Delete()
	{
		if (empty($this->data['ticket_id'])) die('E:\Projects\Omni\ohd_new\lib\Classes\ticket.class.php: 172');

		$ticket_data = array('ticket_id' => $this->data['ticket_id']);

		$this->db->qD('#_PREF_tickets',                       $ticket_data);
		$this->db->qD('#_PREF_tickets_products_forms_values', $ticket_data);
		$this->db->qD('#_PREF_tickets_messages',              $ticket_data);
		$this->db->qD('#_PREF_tickets_history',               $ticket_data);        
	}	
	
	function GetAdditionalFileds()
	{
		// get form items
		$db->q("
			SELECT
			   ff.ticket_field_id,
			   ff.ticket_filed_pos,
			   ff.ticket_field_caption,
			   ff.ticket_field_type,
			   ff.ticket_field_is_optional,
			   fv.ticket_field_value
			FROM #_PREF_tickets_products_form_fields ff
			   LEFT JOIN #_PREF_tickets_products_forms_values fv ON
			      fv.ticket_product_id = ff.ticket_product_id
			      AND fv.ticket_field_id = ff.ticket_field_id
			      AND ticket_id = '{$this->data['ticket_id']}
			WHERE ff.ticket_product_id = {$this->data['ticket_product_id']}
			ORDER BY ff.ticket_filed_pos
		");
		
		$form_items = array();
		while ($data = $db->fetchAssoc()) $form_items[] = $data;
	}
	
	/**
	 * Returns array of system options
	 */
	function GetSysOptions()
	{
		if (is_array($this->sys_options)) return $this->sys_options;
		$dbIni = new sxDbIni();        
		return $this->sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
	}
	
	function GetTicketCaption($sub_part = "") 
	{
		$subject_pref = "Ticket ". $this->data['ticket_num'] ."";
		if (!empty($this->data['caption'])) $subject_pref .= " - '". $this->data['caption'] ."'";
		
		if ($sub_part) $subject_pref .= $sub_part;
		return $subject_pref;
	}
	
	function GetUnansweredStatus()
	{
		$sys_options = $this->getSysOptions();
		if (!empty($sys_options['tickets']['status_for_unanswered'])) $status = $sys_options['tickets']['status_for_unanswered'];
		else if (!empty($sys_options['tickets']['status_for_reopened'])) $status = $sys_options['tickets']['status_for_reopened'];
		else if (!empty($sys_options['tickets']['status_for_new'])) $status = $sys_options['tickets']['status_for_new'];
		else $status = 'Open';
		
		return $status;
	}
	
	/**
	 * Set ticket closed_at date to null and set ticket status (unique for system)
	 */
	function Open($user_id, $open_status)
	{
		$this->UpdateProperty($user_id, 'status', $open_status);
	}
	
	/**
	 * Send notify email for defined action
	 * @param     string    $notify_about    string which represent notify email type
	 *                                       'ticket_created', '!ticket_created_by_advisor', 'ticket_renamed_by_advisor', 
	 *                                       'ticket_closed_by_advisor', 'ticket_new_message'
	 * @param     string    $send_to         email reciever (if null get by default - ticket`s customer)
	 */
	function SendNotifyEmail($notify_about, $send_to = null, $email_variables = null)
	{
		// TODO: check 'ticket_email_customer', check $send_to
		
		
		// aliases
		$sys_options = $this->getSysOptions();
		$templates   = $sys_options['emails_templates'];
		
		// send to
		if ($send_to === null) {
			$send_to = $this->data['customer_email'];
		}
		
		// generate caption
		$subject = $this->GetTicketCaption($sys_options['emails_templates']["{$notify_about}_subject"]);
		
		// generate message
		$message = $sys_options['emails_templates'][$notify_about];
		
		// init mailer
		$mailer = new OhdMail();
		
		// add email variables
		$mailer->AddVariables(
			array (
				'ticket_url' => $sys_options['common']['ticket_form_url'].'?ticket_id='. $this->data['ticket_num'] .'&email='. $this->data['customer_email'] .'&button=View+ticket',
			)
		);
		
		if ($email_variables !== null) {
			$mailer->AddVariables($email_variables);
		}
		
		// send notify email
		$res = $mailer->SendEx($send_to, $subject, $message);
		return !pear::isError($res);
	}
	
	/**
	 * Send notify email for defined action
	 * @param     string    $option_name    ex option name
	 * @param     string    $option_value   ex opiton value
	 */
	function setAddOptions($option_name, $option_value)
	{
		$this->data['add_options'][$option_name] = $option_value;
		var_dump($option_value);
		$this->db->qI(
			'#_PREF_tickets', 
			array('add_options' => serialize($this->data['add_options'])), 
			'UPDATE', 
			array('ticket_id' => $this->data['ticket_id']));
	}
	
	function SetUpdatedNow($user_id, $update_status = true)
	{
		$this->db->qI(
			'#_PREF_tickets', 
			array(
				'modified_at' => 'NOW()',
				'is_in_trash_folder' => 0
			), 
			'UPDATE', array('ticket_id' => $this->data['ticket_id']));
		
		
		if ($update_status)
		{
			$sys_options = $this->getSysOptions();
			//if ($this->data['status'] == $sys_options['tickets']['status_for_closed'])
			{
				$this->Open($user_id, $sys_options['tickets']['status_for_new']);
			}
		}
	}
	

	/**
	 * Set up is_in_trash_folder-flag
	 */
	function TrashIt($user_id)
	{
		$ticket_data  = array('is_in_trash_folder' => 1);        
		$ticket_where = array('ticket_id' => $this->data['ticket_id']);
		$this->db->qI('#_PREF_tickets', $ticket_data, 'UPDATE', $ticket_where);
		if ($user_id) $this->AddHistoryNote($user_id, 'Moved To Trash Folder');
	}
	
	function UpdateCustomerLastOpenDate($d = 'NOW()')
	{
		if (empty($this->data['ticket_id'])) die('E:\Projects\Omni\ohd_new\lib\Classes\ticket.class.php: 257');
		$this->db->qI('#_PREF_tickets', array('customer_last_open_date' => $d), 'UPDATE', array('ticket_id' => $this->data['ticket_id']));
	}

	function UpdateLastMessagePostedByAdmin($d = 1)
	{
		if (empty($this->data['ticket_id'])) die('E:\Projects\Omni\ohd_new\lib\Classes\ticket.class.php: 257');
		$this->db->qI('#_PREF_tickets', array('last_message_posted_by_admin' => $d), 'UPDATE', array('ticket_id' => $this->data['ticket_id']));
	}
	
	function UpdateProperty($user_id, $pr_name, $pr_value)
	{
		if (empty($this->data['ticket_id'])) die('E:\Projects\Omni\ohd_new\lib\Classes\ticket.class.php: 260');
		
		// check value
		//if ($this->data[$pr_name] == $pr_value) return 'Ok!';
		$this->data[$pr_name] = $pr_value;
		
		// update property
		$this->db->qI('#_PREF_tickets', array($pr_name => $pr_value), 'UPDATE', array('ticket_id' => $this->data['ticket_id']));

		// control variables
		$sys_options       = $this->getSysOptions();
		$is_closing_ticket = $pr_name == 'status' && $pr_value == $sys_options['tickets']['status_for_closed'];
		

		// close ticket
		if ($is_closing_ticket) {
			// update closed_at date
			$this->db->qI('#_PREF_tickets', array('closed_at' => 'NOW()'), 'UPDATE', array('ticket_id' => $this->data['ticket_id']));
			
			// doesn't update status - and do not set status for new ticket
			$this->SetUpdatedNow($user_id, false);
		}
		// other statuses
		else {
			if ($pr_name == 'status' && 
				($pr_value == $sys_options['tickets']['status_for_new'] || $pr_value == $sys_options['tickets']['status_for_reopened']))
			{
				// update closed_at date
				$this->db->qI('#_PREF_tickets', array('closed_at' => 'NULL'), 'UPDATE', array('ticket_id' => $this->data['ticket_id']));
				
			}
			
			$this->SetUpdatedNow($user_id, false);
		}
		
		
		// notify only selected options
		if (!is_array($pr_name, array('status', 'priority', 'assigned_to'))) return true;
		
		
		
		

		// emails notifications
		$history_note = "Chanded $pr_name to $pr_value";
		
		$dbIni  = new sxDbIni($this->db);        
		$users  = new Users($this->db);
		
		// GET USER EMAILS AND NAME
		if (is_numeric($user_id) && $user_id > 0)
		{
			$user_data    = $users->GetUserData($user_id);
			$user_options = $dbIni->LoadIni(DB_PREF.'users_options', array('user_id' => $user_id));
			//$user_options =  $user->GetOptions();
			$user_email   = $user_data['user_email'];
			$user_fname   = "{$user_data['user_name']} {$user_data['user_lastname']}";
		}
		else {
			$user_email = "slyder@homa.net.ua";
			$user_email = "KG";
		}
		
		// SEND NOTIFICATION EMAIL
		$email_addr    = $user_email;
		$email_subj    = 'Subj';
		$email_message = 'Message';
		$send_email = true;

		switch ($pr_name)
		{
			case 'assigned_to':
				$user_data    = $users->GetUserData($pr_value);
				$user_options = $dbIni->LoadIni(DB_PREF.'users_options', array('user_id' => $user_id));
				$email_addr   = $user_data['user_email'];
				$user_fname   = "{$user_data['user_name']} {$user_data['user_lastname']}";
				
				$send_email = !empty($user_options['notification_emails']['defect_assigment']);
				if (!$pr_value) {
					$history_note   = "Reasigned to NULL";
					$email_subj     = $this->GetTicketCaption(' unassigment');
					$email_message  = "Ticket {$this->data['ticket_num']} - '{$this->data['caption']}' has been unassigned.";
				}
				else {
					$history_note   = "Reasigned to {$user_fname}";
					$email_subj     = $this->GetTicketCaption(' assigment');
					$email_message  = "Ticket {$this->data['ticket_num']} - '{$this->data['caption']}' has been assigned to you.";
				}
				break;
				
			case 'type':
			case 'status':
			case 'priority':
				$send_email    = !empty($user_options['notification_emails']['defect_changed']);
				$email_subj     = $this->GetTicketCaption(" $pr_name changed");
				$email_message = "Ticket {$this->data['ticket_num']} - '{$this->data['caption']}' $pr_name has been changed to $pr_value";
				break;
		};
		
		// init mailer
		$mailer = new OhdMail();
		$mailer->AddVariables(
			array (
				'ticket_url' => $sys_options['common']['ticket_form_url'].'?ticket_id='.$this->data['ticket_num'].'&email='.$this->data['customer_email'].'&button=View+ticket',
			)
		);
		
		if ($send_email) {
			$res = $mailer->SendEx($email_addr, $email_subj, $email_message);
			$send_result = !pear::isError($res);
		}
		
		// if closing - send norify to customer
		if ($is_closing_ticket) {
			$this->SendNotifyEmail('ticket_closed_by_advisor');
		}

		// add history note
		$this->AddHistoryNote($user_id, $history_note);

		if ($send_email && !$send_result) return 'Property Saved. <span style="color: red;">But problems with sending notification email.</span>';
		return 'Saved Ok!';
	}
	
}