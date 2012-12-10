<?php
/**************************************************************
* Project  : Omni Help Desk
* Module   : Piping
* What     : Class
* Version  : 1.0
* Date     : 2005.01.09
* Modified : $Id: ticket_responser.inc.php,v 1.1 2005/02/10 07:08:58 ForJest Exp $
* Author   : forjest@yahoo.com
**************************************************************
* 
*  Response to ticket
*  Lately will be place in common part
*/

class TicketResponser
{
	var $template;
	var $subject;
	var $from_name;
	var $from_address;
	var $view_script_url;

	function TicketResponser()
	{
		$this-> set_template('', '');
		$this-> set_from_attributes('', '');
	}

	function set_config($config)
	{
		$this-> set_from_attributes($config['from_name'], $config['reply_to']);
		$this-> set_view_script_url($config['view_ticket_script']);
	}

	function set_template($subject, $template)
	{
		$this-> subject  = $subject;
		$this-> template = $template;
	}

	function set_from_attributes($from_name, $from_address)
	{
		$this-> from_name    = $from_name;
		$this-> from_address = $from_address;
	}

	function set_view_script_url($view_script_url)
	{
	     $this-> view_script_url = $view_script_url;
	}

	function prepare_replacements($ticket_info)
	{
		$result = array(
			'!ticket_url!'  => $this-> view_script_url.'?id='.$ticket_info['id']."&Email=".urlencode($ticket_info['Email']),
			'!ticket_id!'   => $ticket_info['id'],
			'!ticket_date!' => date('Y-m-d H:i:s')
		);
		
		foreach ($ticket_info as $field_name => $value)
		{
			$result['!'.$field_name.'!'] = $value;
		}
		return $result;
	}

	function do_response($ticket_info)
	{
		return;
		echo "<pre>";
		var_dump($ticket_info);
		var_dump($this);
		die();

		SendMail($this-> from_name,
		         $this-> from_address,
		         $ticket_info['Email'],
		         $this-> subject,
		         $this-> template,
		         $this-> prepare_replacements($ticket_info));
	}

}
?>