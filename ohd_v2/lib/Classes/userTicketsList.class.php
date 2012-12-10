<?php

/**
 * Tickets list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */
 
require_once 'sx_db_ini.class.php';
require_once 'ticket.class.php';

class userTicketsList
{
	/**
	 * Database object
	 * @var sxDB
	 */
	var $db = null;
	
	/**
	 * Data container
	 * @var array
	 */
	var $data = null;
	
	var $product_id = null;
	
	var $smarty = null;
	
	function array_stripslashes($ar){
	
		foreach($ar as $k=>$v){
			if(is_array($v)) {
				$ar[$k] = $this->array_stripslashes($v);
			}
			else $ar[$k] = stripslashes($v);
		}
		
		return $ar;
	}

	function get_vars()
	{
		if (get_magic_quotes_gpc())
		{
			return $this->array_stripslashes($_REQUEST);
		}
		
		return $_REQUEST;
	}
	
	function userTicketsList()
	{
		$db =& sxDB::instance();
		$this->db =& $db;
		
		$dbIni = new sxDbIni($db);
		$this->config = $dbIni->LoadIni(DB_PREF.'sys_options');
		$this->product_config = $db->queryFetch("SELECT * FROM #_PREF_tickets_products WHERE ticket_product_id = '".$this->product_id."'");
		
		require_once  'SmartyRendererStandAlone.class.php';
		$_smarty = new SmartyRendererStandAlone(BASE_DIR."/templates", NULL, true);
		$this->smarty =& $_smarty->_smarty;
		$this->smarty->assign("vars",$this->get_vars());
		
		$dbIni = new sxDbIni($db);
		$this->sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');

	}
	
	function process_request(&$ticket_form) 
	{
		$db    =& $this->db;
		$vars  = $this->get_vars();
		$error = false;
		
		if (isset($vars["email"]) && isset($vars["ticket_id"]) && trim($vars["email"]) != "" && trim($vars["ticket_id"]) != "")
		{
			$info = $db->queryFetch("
				SELECT * 
				FROM #_PREF_tickets 
				WHERE 
				  ( ticket_id      = '".mysql_escape_string(trim($vars['ticket_id']))."' 
				    OR ticket_num   = '".mysql_escape_string(trim($vars['ticket_id']))."' ) 
				  AND customer_email = '".mysql_escape_string(trim($vars['email']))."'");
			
			if ($info["customer_email"] == $vars["email"]) 
			{
				$ticket = new Ticket($info['ticket_id']);
				$ticket->UpdateCustomerLastOpenDate();
				$ticket->UpdateLastMessagePostedByAdmin(0);
					
				//show ticket list
				if (@$vars["action"] == "send_message") 
				{
					if (true == $this->validateFields($vars, $errors))
					{
						$this->addNewMessage($vars, $info["ticket_id"], $info);
						$this->smarty->assign('redirect_url', $_SERVER["REQUEST_URI"]);
						$this->smarty->display("redirect.html");
					}
					else
					{
						$this->showMessagesList($info['ticket_id'], $errors);
					}
				}
				else
				{
					$this->showMessagesList($info['ticket_id']);
				}
			}
			else
			{
				$error = "Ticket not found or incorrect email entered. try again.";
				$ticket_form = $this->showLoginForm($error);
			}
			//will not use sessions here :)
		}
		else
		{
			$error = false;
			if (isset($vars["email"]) && trim($vars["email"]) == "") $error = "Please enter email";
			if (isset($info["ticket_id"]) && trim($info["ticket_id"]) == "") $error = "Please enter ticket id";
			$ticket_form = $this->showLoginForm($error);
		}
		return true;
	}
	
	function showLoginForm($error = false)
	{
		$smarty =& $this->smarty;
		$smarty->assign("error", $error);
		$smarty->assign("show_ticket_url", $this->sys_options['common']['ticket_form_url']);
		
		return $smarty->fetch("ticket_login_form.html");
	}
	
	function showMessagesList($ticket_id, $error = false) 
	{
		$smarty =& $this->smarty;
		$db =& $this->db;
		
		$ticket = $this->getTicket($ticket_id);
		$this->ticketInfo = $ticket;
		$ticket_messages = $this->getTicketMessages($ticket_id);
		
		
		$smarty->assign("ticket",$ticket);
		$smarty->assign("messages", $ticket_messages);
		$smarty->assign("error", $error);
		$smarty->display("messages_list.html");
		//die("implement showMessagesList please!");
	}
	
	function getTicket($ticket_id) 
	{
		$db =& $this->db;
		$info = $db->queryFetch("
			SELECT 
			   t.ticket_id,
			   t.ticket_num,
			   t.group_id,
			   t.ticket_product_id,
			   t.creator_user_id,
			   t.creator_user_name,
			   t.creator_user_ip,
			   t.caption,
			   t.description,
			   t.description_is_html,
			   t.customer_name, 
			   t.customer_email,
			   t.customer_phone,
			   t.status,
			   t.priority,
			   t.type,
			   DATE_FORMAT(t.created_at,  '%Y-%m-%d') AS created_at,
			   DATE_FORMAT(t.modified_at, '%Y-%m-%d') AS modified_at,
			   DATE_FORMAT(t.closed_at,   '%Y-%m-%d') AS closed_at,
			   CONCAT(o.user_name, ' ', o.user_lastname)             AS ticket_owner_name,
			   CONCAT(a.user_name, ' ', a.user_lastname)             AS ticket_assigned_to_name,
			   IF(a.user_id = '' || a.user_id = 0, NULL,  a.user_id) AS ticket_assigned_to_id,
			   modified_at AS modified_at_order,
			   modified_at AS modified_at_order,
			   created_at  AS created_at_order,
			   gr.group_caption
			
			FROM 
			   #_PREF_tickets t
			   LEFT JOIN #_PREF_users o ON o.user_id = t.creator_user_id
			   LEFT JOIN #_PREF_users a ON a.user_id = t.assigned_to
			   LEFT JOIN #_PREF_groups gr ON gr.group_id = t.group_id
			
			WHERE 
			   ticket_id=".$ticket_id
		);
		$info["fields"] = $this->getAdditionalFields($ticket_id, $info);
		
		return $info;
	}
	
	function getAdditionalFields($ticket_id, $info) 
	{
		$ticket_product_id = $info["ticket_product_id"];
		$result["Caption"] = $info["caption"];
		$result["Message"] = $info["description"];
		$this->db->q("
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
		         AND ticket_id = '$ticket_id'
		    WHERE ff.ticket_product_id = $ticket_product_id
		    ORDER BY ff.ticket_filed_pos
		");
		$form_items = array();
		while ($data = $this->db->fetchAssoc()) 
		{
			$result[$data["ticket_field_caption"]] = $data["ticket_field_value"];
		}
		return $result;
	}
	
	function getTicketMessages($ticket_id)
	{
		$db =& $this->db;
		$res = $db->query("
			SELECT 
				#_PREF_tickets_messages .*,
				#_PREF_users.user_name 
			FROM #_PREF_tickets_messages 
				LEFT JOIN #_PREF_users on
				  #_PREF_tickets_messages.message_creator_user_id = #_PREF_users.user_id 
			WHERE ticket_id=".$ticket_id." AND message_type='message'
			ORDER BY #_PREF_tickets_messages.message_datetime
		");
		
		$res = $db->q("
			SELECT 
			   m.message_id,
			   m.message_creator_user_id,
			   m.message_creator_user_name,
			   m.message_type,
			   m.message_text,
			   m.message_text_is_html,
			   message_atachment_file,
			   message_atachment_name,
			   DATE_FORMAT(m.message_datetime, '%H:%y of %Y-%m-%d') AS message_datetime_formated,
			   CONCAT(o.user_name, ' ', o.user_lastname) AS message_owner_name
			FROM 
			   #_PREF_tickets_messages m
			   LEFT JOIN #_PREF_users o ON o.user_id = m.message_creator_user_id
			WHERE
			   ticket_id = $ticket_id 
			   AND m.message_type != 'note'
			ORDER BY 
			   m.message_datetime DESC
		");
		
		$data = array();
		while ($rec = $db->fetchArray($res)) 
		{
			 if ($rec['message_creator_user_id'])
			 {
			 	 $rec['header_style'] = "background-color: #DD9090;";
			 }
			 else
			 {
				 switch ($rec['message_type'])
				 {
					 case 'note': $rec['header_style'] = "background-color: #90DD95;"; break;
					 default:     $rec['header_style'] = "background-color: #90AADD;"; break;
				 }
			}
			
			/*if ($rec["user_name"] == NULL) 
			{
				$rec["message_type"] = "user";
				$rec["user_name"] = $this->ticketInfo["customer_name"];
			}
			else
			{
				$rec["message_type"] = "tech";
			}*/
			
			require_once('HTML/BBCodeParser.php');
			$config = parse_ini_file(BASE_DIR.'/templates/BBCodeParser.ini', true);
			$options = &PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
			$options = $config['HTML_BBCodeParser'];
			unset($options);

			$parser = new HTML_BBCodeParser();
			if ($rec['message_text_is_html'])
			{
				//$rec['message_text'] = wordwrap($rec['message_text'], 100, "<br />\n");
			}
			else
			{
				$parser->setText(htmlspecialchars($rec['message_text']));
				$parser->parse();
				$message_text = $parser->getParsed();
				$rec['message_text'] = htmlwrap($message_text, 100);
			}

			$data[] = $rec;
		}
		return $data;
	}
	
	function addNewMessage($vars, $ticket_id, $user_info)
	{
		$db =& $this->db;

		$ticket = new Ticket($ticket_id);
		$params = array();
		$params['message_creator_user_id']    = 0;
		$params['message_creator_user_name '] = $ticket->data['customer_name'];
		$ticket->AddMessage('message', $vars["message"], null, $params);
		
		// NOTIFY OPERATOR ABOUT NEW MESSAGE IN ASSIGNED TICKET
		$assigned_to = $ticket->data['assigned_to'];
		if (!empty($assigned_to)) {
			$users = new Users($db);
			$user_data = $users->getUserData($assigned_to);
			
			if ($user_data['user_email'] && $message_data['message_type'] != 'note') {
				$mailer = new OhdMail();
				$mailer->AddVariables(
					array (
						'ticket_url'     => "{$sys_options['common']['ticket_form_url']}?ticket_id={$ticket_id}&email={$ticket->data['customer_email']}&button=View+ticket",
						'ticket_message' => $message_data['message_text'],
						'ticket_text'    => $message_data['message_text']
						
					)
				);
				
				$email_subj = "Ticket Num. {$ticket->data['ticket_num']} - '{$ticket->data['caption']}' new message notification from customer";
				$email_message = "THIS IS AN AUTO-GENERATED EMAIL FROM AQUEOUS TECHNOLOGIES' AUTOMATED HELP DESK. <br />".
								 "PLEASE DO NOT REPLY TO THIS EMAIL.";
				
				$from_name = $sys_options['common']['company_name'] . " ticket #{$ticket->data['ticket_num']}";
				$mailer->SendEx($user_data['user_email'], $email_subj, $email_message, array('from' => $from_name));
			}
		}
 		
 		return true;
	}
	
	function validateFields($vars, &$errors) 
	{
		// IPs bans
		if (!empty($this->sys_options['tickets_list']['banned_ips']))
		{
			if (preg_match("/^\s*{$_SERVER['REMOTE_ADDR']}\s*$/m", $this->sys_options['tickets_list']['banned_ips'], $match))
			{
				$errors = 'Your IP adress has been banned.';
				return false;
			}
		}
		
		
		// emails bans
		if (!empty($this->sys_options['tickets_list']['banned_emails']) && !empty($vars["email"]))
		{
			if (preg_match("/^\s*{$vars['email']}\s*$/m", $this->sys_options['tickets_list']['banned_emails'], $match))
			{
				$errors = 'Your email adress has been banned.';
				return false;
			}
		}

		// flood protection
		if ($this->sys_options['tickets_list']['flood_protection'])
		{
			$cnt = $this->db->getOne(
				"SELECT COUNT(*) FROM #_PREF_tickets_messages", 
				array (
					'message_creator_user_ip' => $_SERVER['REMOTE_ADDR'],
				    '#PLAIN' => "message_datetime > (NOW() - INTERVAL ". $this->sys_options['tickets_list']['flood_protection'] ." SECOND)"
				)
			);
			
			if ($cnt > 0) 
			{
				$errors = "Flood protection system: ".'please wait for '.$this->sys_options['tickets_list']['flood_protection'].' seconds';
				return false;
			}
		}

		if (trim($vars["message"]) == "") 
		{
			$errors = "Please enter message!";
			return false;
		}
		return true;
	}
	
}

?>