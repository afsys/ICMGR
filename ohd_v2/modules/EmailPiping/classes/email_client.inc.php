<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Class for processing email retrivieving
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 10, 2006
 * @version    1.00 Beta
 */

require_once 'lib/PEAR/Net/POP3.php';
require_once 'lib/PEAR/Mail/mimeDecode.php';
require_once 'modules/EmailPiping/classes/email_body.inc.php';

define('EC_PERCENTS', 80);
class EmailClient
{
	var $pop3;
	var $have_error;
	var $error_message;
	var $uid2number;
	var $connected;
	var $EmailBody;

	function EmailClient($hostname, $login, $password, $port = 110)
	{
		 $this->pop3          =& new Net_POP3(); 
		 $this->have_error    =  false;
		 $this->error_message =  '';
		 $thsi->connected     = false;
		 $this->connected     = false;
		 $this->connect($hostname, $login, $password, $port);
		 $this->EmailBody     = &new EmailBody();
	}

	function connect($hostname, $login, $password, $port)
	{
		 if (PEAR::isError($ret = $this->pop3->connect($hostname, $port)))
		 {
			  $this->error_message = $ret->getMessage();
			  return false;
		 }
		 
		 if (PEAR::isError($ret= $this->pop3->login($login, $password, 'USER')))
		 {
			  $this->error_message = $ret->getMessage();
			  return false;
		 }

		 $this->connected  = true;
		 $this->have_error = false;
		 return false;
	}

	function disconnect()
	{
		if ($this->is_connected())
		{
			$this->pop3->disconnect();
		}
	}


	function is_connected()
	{
		return $this->connected;
	}

	function have_error()
	{
		return $this->have_error;
	}

	function get_last_error()
	{
		return $this->error_message;
	}
	
	function get_message_uids()
	{
		$this->have_error = true;
		if (!$this->is_connected()) {
			  $this->error_message = __('Could not connect');
			  return false;
		}

		if (!($tmp = $this->pop3->getListing())) {
			  $this->error_message = __("Can't get listing").' ('.__("possibly there is no new emails").")";
			  return false;
		}
		$this->uid2number = array();
		
		foreach ($tmp as $email_stats) {
			  $this->uid2number[$email_stats['uidl']] = $email_stats['msg_id'];
		}
		$this->have_error = false;
		return array_keys($this->uid2number);
	}
	
	
	function clearEmailBox($handled_emails)
	{
		$this->have_error = true;
		if (!$this->is_connected()) {
			  $this->error_message = __('Could not connect');
			  return false;
		}

		if (!($tmp = $this->pop3->getListing())) {
			  $this->error_message = __("Can't get listing").' ('.__("possibly there is no new emails").")";
			  return false;
		}
		
		$handled_emails_uids = array();
		foreach ($handled_emails as $handled_email) $handled_emails_uids[] = $handled_email['uid'];
		//dump($handled_emails_uids);
		
		foreach ($tmp as $v) {
			if (in_array($v['uidl'] , $handled_emails_uids)) {
				$this->pop3->deleteMsg($v['msg_id']);
				//dump('DELE '.$v['msg_id']);
			}
		}
		
		return $tmp;
	}
	
	function form_email($uid)
	{
		$msg_num = $this->uid2number[$uid];
		$headers = $this->pop3->getParsedHeaders($msg_num);
		
		if (empty($headers['From'])) {
			// return false;
		}
		
		// echo "$uid <br />";
		
		$this->EmailBody->process_raw_message($this->pop3->getMsg($msg_num));
		
		
		// @TODO: replace code from Horde by code from PEAR
		require 'lib/Horde/MIME.php';
		//$headers['Subject'] = '=?iso-8859-1?Q?Exposici=F3n_del_FARO';
		//$headers['Subject'] = '=?iso-8859-1?Q?CENTRO_CULTURAL_SAN_MARTIN_de_la_Ciudad_Aut=F3noma_de_Buen?= =?iso-8859-1?Q?os_Aires?=';
		//$headers['Subject'] = '=?Cp1252?Q?Western_Union_Money_Transfer=AE_Pick_Up_Notification.?=';
		
		
		/* global $lang_info;
		var_dump($lang_info);
		die('a'); */

		
		$headers['Subject'] = $headers['Subject'] ? HordeMIME::decode($headers['Subject'], 'iso-8859-1') : 'No Subject';  
		$headers['From']    = $headers['From']    ? HordeMIME::decode($headers['From'],    'iso-8859-1') : 'no data';  

		
		/*var_dump($this->EmailBody->get_text_message());
		die('a');*/
		
		 
		return array(
			'headers'  => $headers,
			'from'     => $headers['From'],
			'to'       => $headers['To'],
			'cc'       => $headers['Cc'],
			'subject'  => $headers['Subject'],
			'body'     => $this->EmailBody->get_text_message(),
			'uid'      => $uid,
			'attaches' => $this->EmailBody->get_attaches()
		);
	}

	function get_emails($uids)
	{
		if (empty($uids)) {
			return array();
		}
		
		$this->have_error = true;
		if (!$this->is_connected()) {
			$this->error_message = __('Could not connect');
			return false;
		}
		$this->have_error = false;
		
		$result = array();
		foreach (array_intersect(array_keys($this->uid2number), $uids) as $uid) {
			if ($email = $this->form_email($uid)) {
				$result[] = $email;
			}
			else {
				//var_dump($email);
			}
		}
		
		return $result;
	}

	function mime_get_all_parts($structure, &$result)
	{
		 if (isset($structure->body)) {
			  $result[] = (array)$structure;
			  return;
		 }
		 
		 if (isset($structure->parts)) {
			  foreach ($structure->parts as $part) {
				   $this->mime_get_all_parts($part, $result);
			  }
			  return;
		 }
	}

	function clear_html_tags($contents)
	{
		if (preg_match_all("~<body.*>(.*)</body>~Usi", $contents, $M)) {
			return trim(implode(',', $M[1]));
		}
		return '';
	}

	function format_text_message($type, $contents)
	{
		if ('plain' == $type) {
			return nl2br($contents);
		}
		return $this->clear_html_tags($contents);
	}

}
?>