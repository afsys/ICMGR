<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * This class makes delivery. Agregation of all parts.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 10, 2006
 * @version    1.00 Beta
 */
 

require_once 'modules/EmailPiping/classes/email_client.inc.php';
require_once 'modules/EmailPiping/classes/email_ticket.inc.php';
require_once 'modules/EmailPiping/classes/messages_tosser.inc.php';
require_once 'modules/EmailPiping/classes/ticket_responser.inc.php';

define('EPD_MESSAGES_AT_ONCE', 25);
define('EPD_DEFAULT_GROUP', 1);

define('EPD_ERROR_CONFIG_INCORRECT', 'Email Piping configuration error. You need to modify settings for Email Piping.');
define('EPD_ERROR_NO_RULES', 'No filtering rules set up. You need add at least one filtering rules');


class TicketDelivery
{
	var $Config;
	var $error_message;
	var $at_once;
	var $delivered_count;
	var $db;
	var $user;
	var $FilteringRules;
	var $UnreadEmails;
	var $SystemConfiguration;
	var $Responser;

	function TicketDelivery(&$db, &$user)
	{
		$this->db = sxDB::instance();
		$this->user = &$user;
		
		$this->DbTicket =& new EmailTicket($this->db, $this->user);

		$this->error_message = '';
		$this->at_once       = EPD_MESSAGES_AT_ONCE;
		$this->delivered_count = 0;
		$this->setup_responser();
	}

	function SetConfigProvider(&$Config)
	{
		$this->error_message   = '';
		$this->delivered_count = 0;
		$this->Config          = &$Config;
	}

	function Shutdown()
	{
		if (!empty($this->Emails)) {
			$this->Emails->disconnect();
		}
	}

	function get_error_message()
	{
	     return $this->error_message;
	}

	function get_delivered_count()
	{
	     return $this->delivered_count;
	}

	function perform_check()
	{
		$this->error_message = '';
		if (!$this->Config->IsConfigCorrect()) {
			$this->error_message = EPD_ERROR_CONFIG_INCORRECT;
			return false;
		}

		if (!$this->request_filtering_rules()) {
			$this->error_message = EPD_ERROR_NO_RULES;
			return false;
		}
		return true;
	}

	function attachments_allowed()
	{
	     return true;
	}

	function SetupEmailClient()
	{
		$Config = $this->Config->GetConfig();

		$this->Emails = &new EmailClient($Config['acc_host'], $Config['acc_login'], $Config['acc_pass'], $Config['acc_port']);
		if ($this->Emails->have_error()) {
			$this->error_message = $this->Emails->get_last_error();
			return false;
		}
		return true;
	}

	function setup_responser()
	{
		return;
		$this->Responser = &new TicketResponser();
		$this->Responser->set_config($this->SystemConfiguration);
		$this->Responser->set_template
		      ($this->SystemConfiguration['submit_subject'], 
		       $this->SystemConfiguration['submit_msg']);
	}

	function set_at_once($at_once)
	{
		$this->at_once = (int)$at_once;
	}

	function request_filtering_rules()
	{
		$query = 'SELECT * FROM #_PREF_email_filter ORDER BY filter_order';
		$this->db->q($query);
		$rules = array();
		while ($r = $this->db->fetchAssoc()) $rules[] = $r;
		$this->FilteringRules = $rules;
		return count($this->FilteringRules);
	}

	function get_message_uids()
	{
		$result = array();
		$result = $this->Emails->get_message_uids();

		if ($this->Emails->have_error()) {
			$this->error_message = $this->Emails->get_last_error();
			return array();
		}

		return $result;
	}


	function get_unread_emails($uids)
	{
		$this->UnreadEmails = array();
		if (empty($uids)) {
			return false;
		}

		$this->UnreadEmails = $this->Emails->get_emails($this->DbTicket->get_unread_uids($uids, $this->at_once));

		if ($this->Emails->have_error()) {
			$this->error_message = $this->Emails->get_last_error();
			return false;
		}

		$this->delivered_count = count($this->UnreadEmails);
		return (bool)$this->delivered_count;
	}

	function handle_emails()
	{
		$this->Tosser = &new MessagesTosser($this->FilteringRules, EPD_DEFAULT_GROUP);
		
		$skip_db_uids_handling = !empty($this->Config->data['delete_email']) && $this->Config->data['delete_email'] ? true : false;
		
		foreach ($this->UnreadEmails as $email) {
			if (!$this->DbTicket->make_tiket_from_email($email, $this->Tosser->determine_rule_id($email), $skip_db_uids_handling)) {
				$this->error_message = $this->DbTicket->get_error_message();
				return false;
			}
			//$this->Responser->do_response($this->DbTicket->get_last_ticket_info());
		}
		return true;
	}


	function DoDelivery()
	{
		if (!$this->perform_check())  {
			return false;
		}

		if (!$this->SetupEmailClient()) {
			return false;
		}

		$messages_uids = $this->get_message_uids();
		
		$have_unreaded = $this->get_unread_emails($messages_uids);
		
		if (!$have_unreaded) {
			$r = true;
		}
		else {
			$r = $this->handle_emails();
		}
		
		// delete emails if have option to delete
		if (!empty($this->Config->data['delete_email']) && $this->Config->data['delete_email']) {
			$this->Emails->clearEmailBox($this->UnreadEmails);
		}
		
		
		$this->Emails->disconnect();
	     
		return $r;
	}
	
	function MakeFullDelivery(&$user) {
		$delivery =& new TicketDelivery($this->db, $user);
		$config   =  new EmailPipingConfigList();
		$configs  =  $config->GetConfigs();
		
		$results = array();
		
		foreach ($configs as $cfg) {
			if ($cfg->data['acc_disabled']) {
				$results[] = array (
					'server'    => $cfg->data['acc_host'],
					'message'   => 'disabled',
					'delivered' => 0
				);
			}
			else {
				$delivery->SetConfigProvider($cfg);
				$delivery->DoDelivery();
				
				$results[] = array (
					'server'    => $cfg->data['acc_host'],
					'message'   => $delivery->get_error_message(),
					'delivered' => $delivery->get_delivered_count()
				);
				
				$delivery->Shutdown();
			}
		}
		
		return $results;
	}
}
?>