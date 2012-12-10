<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Class for processing emails with filters.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 10, 2006
 * @version    1.00 Beta
 */

require_once 'lib/html2bb.function.php';
require_once 'lib/Classes/ticket.class.php';
require_once 'lib/Classes/sx_db_ini.class.php';

class EmailTicket
{
	var $db;
	var $user;
	var $body_field;
	var $error_message;
	var $process_attaches;
	var $allowed_extensions;
	var $attachments_dir;
	var $last_ticket_data;

	function EmailTicket(&$db, &$user)
	{
		$this->db = &$db;
		$this->user = &$user;
		$this->error_message = '';
		$this->process_attaches = false;
	}
	
	function set_body_field($body_field)
	{
		$this->body_field = $body_field;
	}

	function set_attachment_config($attachments_dir, $allowed_extensions)
	{
		$this->process_attaches = true;
		$this->attachments_dir  = rtrim($attachments_dir, '\\/');
		$this->allowed_extensions = $allowed_extensions;
	}

	function get_error_message()
	{
		return $this->error_message;
	}

	function get_sender_email($from)
	{
		if (!preg_match("~<([^>]+)>~", $from, $M)) {
			return trim($from);
		}
		return $M[1];
	}

	function get_sender_name($from)
	{
		if (preg_match('~("?)([^>"\']+)\\1\s*<~', $from, $M))  {
			return trim($M[2]);
		}
		return $from;

	}

	/**
	 * Returns first ticket Id from database by ticket number
	 * @param     array      $numArr       array of possible ticket numbers     
	 * @return    integer    ticket_id or false
	 */
	function getFirstTicketId($numArr)
	{
		if (!is_array($numArr)) return false;
		foreach ($numArr as $ticket_num) {
			$id = $this->db->getOne("SELECT ticket_id FROM #_PREF_tickets", array('ticket_num' => $ticket_num));
			if ($id != "") return $id;
		}
		return false;
	}

	function make_tiket_from_email($Email, $filter_conf, $skip_db_uids_handling)
	{
		include_once "Classes/sx_db.class.php";
		$sxDb =& sxDB::instance();
		
		// no filters - skip email
		if ($filter_conf === null) {
			// init email log array
			$email_log_data = array (
				'email_uid'      => $Email['uid'],
				'email_subject'  => $Email['subject'],
				'email_headers'  => serialize($Email['headers']),
				'email_body'     => $Email['body'],
				'email_added_at' => 'NOW()'
			);
			
			$email_log_data['email_status'] = 'unmatched';
			if (!$skip_db_uids_handling) $sxDb->qI('#_PREF_email_handled_uid', array('uid' => $Email['uid']));
			$sxDb->qI('#_PREF_piping_emails_log', $email_log_data);
			return true;
		}
		
		// get ticket number from to/subject
		if (preg_match_all("/(?:Ticket Num\.|#)\s+(\d+)/", $Email["to"].$Email["subject"],$matches)) {
			$prev_ticket_id = $this->getFirstTicketId($matches[1]);
		}
		else {
			// if don't have ticket with same number - try to get with same email
			$id = $this->db->getOne("SELECT ticket_id FROM #_PREF_tickets", array('customer_email' => $this->get_sender_email($Email["from"])));
			if ($id != "") $prev_ticket_id = $id;
			else $prev_ticket_id = false;
		}
		
		// prepare CC array
		$ccs = preg_split('/,|;/', $Email['cc']);
		if (is_array($ccs)) {
			foreach ($ccs as $k=>$v) {
				$ccs[$k] = $this->get_sender_email($v);
			}
			$ccs = serialize($ccs);
		}
		else $ccs = '';
		
		$dbIni =  new sxDbIni($sxDb);
		$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
		
		// init spam filter object
		require_once 'spam_filter.inc.php';
		$spamFilter = new SpamFilter();
		$spamFilter->SetVocabubaries(
			!empty($sys_options['piping_spam_filter']['words_in_caption']) ? $sys_options['piping_spam_filter']['words_in_caption'] : null,
			!empty($sys_options['piping_spam_filter']['words_in_body'])    ? $sys_options['piping_spam_filter']['words_in_body']    : null
		);
		
		if ($spamFilter->Match($Email) || preg_match("/\b". $this->get_sender_email($Email["from"]) ."\b/", $sys_options['piping_spam_filter']['banned_emails'], $match)) {
			// @TODO: добавить вывод того что сообщение пропущено на страницу 
			$email_log_data['email_status'] = 'spam';
			if (!$skip_db_uids_handling) $sxDb->qI('#_PREF_email_handled_uid', array('uid' => $Email['uid']));
			return true;
		}
		
		
		// init email log array
		$email_log_data = array (
			'email_uid'      => $Email['uid'],
			'email_subject'  => $Email['subject'],
			'email_headers'  => serialize($Email['headers']),
			'email_body'     => $Email['body'],
			'email_added_at' => 'NOW()'
		);
		
		// ignore email because of check for loop
		$loop_free = true;
		if ($this->get_sender_email($Email["from"]) == $this->get_sender_email($Email["to"])) {
			//$loop_free = false;
			$Email["from"] = 'loop_avoided';
			
			// @TODO: добавить вывод того что сообщение пропущено на страницу 
			$email_log_data['email_status'] = 'loop_avoided';
			if (!$skip_db_uids_handling) $sxDb->qI('#_PREF_email_handled_uid', array('uid' => $Email['uid']));
		}
		
		// LOOP_FREE
		if ($loop_free) {
			// ignore email because of X-Loop
			if (!empty($Email['headers']['X-Loop'])) {
				// @TODO: добавить вывод того что сообщение пропущено на страницу 
				$email_log_data['email_status'] = 'x-loop';
				if (!$skip_db_uids_handling) $sxDb->qI('#_PREF_email_handled_uid', array('uid' => $Email['uid']));
			}
			// add emails as ticket message
			else if ($prev_ticket_id != false && @$filter_conf['add_email_as'] == 'message_if_can') {
				$ticket = new Ticket($prev_ticket_id);
				
				$params = array();
				$params['message_subject']            = $Email['subject'];
				$params['message_creator_user_id']    = 0;
				$params['message_creator_user_name']  = $Email['from'];
				
				$new_message_id = $ticket->AddMessage('message', $Email['body'], null, $params);
				
				// atachment processing
				if ($sys_options['attachments']['allow']) {
					$res = $this->processAttachment($ticket, $Email, $sys_options['attachments']['directory'], array());
				}
				
				if (!$skip_db_uids_handling) $sxDb->qI('#_PREF_email_handled_uid ', array('uid' => $Email['uid']));
				
				// notify email data
				$ne_ticket_num     = $ticket->data['ticket_id'];
				$ne_caption        = $ticket->data['caption'];
				$ne_customer_email = $this->get_sender_email($Email['from']);
				
				// set log data
				$email_log_data['added_to_ticket_id']  = $ticket->data['ticket_id'];
				$email_log_data['added_to_message_id'] = $new_message_id;
			}
			
			// add new ticket
			else {
				$ticket_num = Tickets::GenerateUniqId();

				//$email_body = html2bb($Email['body']);
				$email_body = $Email['body'];
				$this->last_ticket_data = array
				(
					'ticket_num'            => $ticket_num,
					'group_id'              => $filter_conf['ticket_group_id'],
					'ticket_product_id'     => $filter_conf['ticket_product_id'],
					  
					'caption'               => strip_tags($Email['subject']),
					'description'           => $email_body,
					'description_is_html'   => 1,
					
					'customer_name'         => $this->get_sender_name($Email['from']),
					'customer_email'        => $this->get_sender_email($Email['from']),
					'carbon_copy_email'     => $ccs,
					  
					'created_at'            => date('Y-m-d H:i:s'),
					'modified_at'           => date('Y-m-d H:i:s'),
					'closed_at'             => null,
					  
					'status'                => $filter_conf['status'],
					'priority'              => $filter_conf['priority'],
					'assigned_to'           => $filter_conf['assigned_to'],
					
					'ticket_email_customer' => 1
				);

				
				$res = $this->db->qI('#_PREF_tickets', $this->last_ticket_data);
				$this->last_ticket_data['id'] = $this->db->getOne('SELECT last_insert_id()');

				//
				$ticket = new Ticket($this->last_ticket_data['id']);
				$ticket->UpdateProperty(0, 'status', $ticket->GetUnansweredStatus());
				
				// atachment processing
				if ($sys_options['attachments']['allow']) {
					$res = $this->processAttachment($ticket, $Email, $sys_options['attachments']['directory'], array());
				}
				
				if (!$skip_db_uids_handling) $sxDb->qI('#_PREF_email_handled_uid', array('uid' => $Email['uid']));
				
				/* // notify email data
				$ne_ticket_num     = $ticket_num;
				$ne_caption        = strip_tags($Email['subject']);
				$ne_customer_email = $this->get_sender_email($Email['from']); */
				

				// set log data
				$email_log_data['added_to_ticket_id'] = $this->last_ticket_data['id'];
				
				// notify customer
				// $ticket->SendNotifyEmail('ticket_created');
			}
			
		} // LOOP_FREE
		
		// add message into log
		$sxDb->qI('#_PREF_piping_emails_log', $email_log_data);
		
		return true;
	}
	

	function get_last_ticket_info()
	{
		 return $this->last_ticket_data;
	}


	/**
	 * Returns array of unread uids
	 * @param     array      $uids     
	 * @param     integer    $count        limit for maximal uids count
	 * @return    array      array of unreaded uids
	 */
	function get_unread_uids($uids, $count)
	{
		 $readed = array();
		 $this->db->q("SELECT uid FROM #_PREF_email_handled_uid", array('uid' => $list));
		 while ($row = $this->db->fetchAssoc()) {
			  $readed[] = $row['uid'];
		 }
		 
		 /* echo "<pre style='text-align: left;'>";
		 echo "readed: <br />";
		 var_dump($readed);
		 echo "</pre>"; /**/     
		 
		 $res = array_slice(array_diff($uids, $readed), 0, $count);

		 /* echo "<pre style='text-align: left;'>";
		 echo "readed 2: <br />";
		 var_dump($res);
		 echo "</pre>"; /**/     

		  
		 return $res;
	}

	/**
	 * Saves file from attachment into attachments folder
	 * @param     array      $email                 email-array
	 * @param     string     $upload_dir            email-array
	 * @param     array      $allowed_extensions    array of allowed extensions
	 * @return    array      array with real filename and logical name
	 */
	function processAttachment($ticket, $email, $upload_dir, $allowed_extensions = array())
	{
		if (!is_dir($upload_dir))
		{
			$this->error_message = 'There was an error storing your attachment -- please try again (try to change access rights)';
			return null;
		}
		$upload_dir = str_replace('\\', '/', realpath($upload_dir).'/');

		
		foreach ($email['attaches'] as $attach)
		{
			// TODO: check extension
			
			// save file
			$filenamepath = md5(uniqid(rand(), true));
			if (!($fd = fopen("{$upload_dir}{$filenamepath}", 'w')))
			{
				$this->error_message .= __("File opening error").". ".$upload_dir.'/'.$attach['filepath'];
				return null;
			}
			fwrite($fd, $attach['body']);
			fclose($fd);

			$res = array (
				'filenamepath' => $filenamepath,
				'filename'     => $attach['filename']
			);
			$ticket->AddMessage('message', '', $res);
		}
		return true;
	}

}

?>