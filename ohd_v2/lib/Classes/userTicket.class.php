<?php
	
/**
 * Tickets list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */
 
require_once 'ticket.class.php';
 
class userTicket
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
	
	function array_stripslashes($ar)
	{
		foreach ($ar as $k=>$v) {
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
	
	function userTicket($product_id)
	{
		$this->product_id = $product_id;
		
		require_once 'sx_mysql.class.php';
		require_once 'sx_db_ini.class.php';

		$db       =& sxDB::instance();
		$this->db =& $db;
		
		$dbIni = new sxDbIni($db);
		$this->config         = $dbIni->LoadIni(DB_PREF.'sys_options');
		$this->sys_options    = $dbIni->LoadIni(DB_PREF.'sys_options');
		$this->product_config = $db->queryFetch("SELECT * FROM #_PREF_tickets_products WHERE ticket_product_id = '".$this->product_id."'");
		
		require_once 'SmartyRendererStandAlone.class.php';
		$_smarty = new SmartyRendererStandAlone(BASE_DIR."/templates","ticket_form.html", true);
		$this->smarty =& $_smarty->_smarty;
	}
	
	function setNotificationEmail($vars)
	{
		require_once 'ticket.class.php';
		$ticket = new Ticket($vars['ticket_id']);
		return $ticket->SendNotifyEmail('ticket_created');
	}
	
	function process_request(&$ticket_form, &$restore_form) 
	{
		$this->vars = $vars = $this->get_vars();
		
		if (!empty($vars['ticket_product_id'])) $this->product_id = $vars['ticket_product_id'];
		
		$this->smarty->assign("vars", $vars);
		$this->smarty->assign("config", $this->config);
		
		$this->smarty->assign("ticket_fields", $this->getTicketFields());
		
		// restore pass
		if (isset($vars["action"]) && $vars["action"] == "rest_ticket" && !empty($vars['rest_value']))
		{
			switch ($vars['rest_by'])
			{
				case 'ticket_num':
					$this->db->q("SELECT * FROM #_PREF_tickets WHERE ticket_num = '{$vars['rest_value']}' OR ticket_id = '{$vars['rest_value']}' ORDER BY modified_at DESC LIMIT 1");
					break;
				
				default:
					$this->db->q("SELECT * FROM #_PREF_tickets WHERE {$vars['rest_by']} = '{$vars['rest_value']}' ORDER BY modified_at DESC LIMIT 1");
					break;
			}
			$data = $this->db->fetchAssoc();
			
			$res = false;
			
			if ($data)
			{
				$v = array (
					'email'      => $data['customer_email'],
					'ticket_id'  => $data['ticket_id'],
					'ticket_id6' => $data['ticket_num'],
					'caption'    => $data['caption'],
				);
				$res = $this->setNotificationEmail($v);
				$res = true;
			}
			else
			{
				$this->smarty->assign("rest_error", "<span style='color: red;'>Incorrect e-mail or ticket ID.<br /></span><br />");
				$this->smarty->assign("rest_value", $vars['rest_value']);
			}
			
			//echo "<pre>";
			//var_dump($this->product_config);
			//die('a');
			
			if ($res)
			{
				header("Location:". $this->product_config["ticket_product_redirect_url"] ."?action=thanks_rest&id=".$v['ticket_id']."&email=".$v['email']);
				exit();
			}
			
		}
		

		// submit ticket form
		if (isset($vars["action"]) && ($vars["action"] == "thanks" || $vars["action"] == "thanks_same" || $vars["action"] == "thanks_rest"))
		{
			$this->smarty->assign("ticket_url", $this->sys_options['common']['ticket_form_url']."?ticket_id=".$vars['id']."&email=".$vars["email"]."&button=View+ticket");
			$this->smarty->assign('id', $vars['id']);
			
			switch ($vars["action"])
			{
				case "thanks":
					$ticket_form = $this->smarty->fetch("thanks.html");
					break;
				case "thanks_rest":
					$restore_form = $this->smarty->fetch("rest_email.html");
					break;
				case "thanks_same":
					$ticket_form = $this->smarty->fetch("same_email_exists.html");
					break;
				default:
					$this->smarty->fetch("same_email_exists.html");
					break;
			}
		}
		else if (isset($vars["action"]) && $vars["action"] == "save_ticket")
		{
			// check previous tickets
			$r = $this->db->q("SELECT * FROM #_PREF_tickets", array('customer_email' => $vars['email']));
			$data = $this->db->fetchAssoc($r);
			//$this->db->numRows($r) > 0

			if ($this->db->numRows($r) > 0 && 0)
			{
				header("Location:". $this->product_config["ticket_product_redirect_url"] ."?action=thanks_same&id=".$data['ticket_id']."&email=".$data['customer_email']);
				exit();
			}
			else
			{
				// validate ticket then save and redirect to another page
				$var_res = $this->validateTicket($vars, $errors);
				if (true !== $var_res) $ticket_form = $this->showTicketForm($errors);
				else
				{
					$this->saveTicket($vars);
					$this->setNotificationEmail($vars);
					if ("" != trim($this->product_config["ticket_product_redirect_url"])) 
					{
						header("Location:". $this->product_config["ticket_product_redirect_url"] ."?action=thanks&id=".$vars['ticket_id']."&email=".$vars['email']);
						exit();
					}
					else
					{
						$this->smarty->assign('id', $vars['ticket_id']	);
						$ticket_form = $this->smarty->fetch("thanks.html");
					}
				}
			}
		}
		else
		{
			//show ticket form
			$ticket_form = $this->showTicketForm();
			//show restore form
			$restore_form = $this->showRestoreForm();
		}
		return true;
	}
	
	function getTicketFields() 
	{
		$db =& $this->db;
		$res = $db->query("
			SELECT * 
			FROM #_PREF_tickets_products_form_fields 
			WHERE ticket_product_id= '". $this->product_id ."'
			ORDER BY ticket_filed_pos
		");
		
		$result = array();
		while ($data = $db->fetchAssoc($res)) 
		{
			if (($data['ticket_field_type'] == 'select' || $data['ticket_field_type'] == 'multiselect') && !empty($data['ticket_field_options'])) {
				$data['ticket_field_options'] = unserialize($data['ticket_field_options']);
			}
			$result[$data["ticket_field_id"]] = $data;
		}
		
		return $result;
	}
	
	function showTicketForm($errors = array())
	{
		if ($this->product_id) {
			$fields = $this->getTicketFields();
			
			$this->smarty->assign("fields", $fields);
			$this->smarty->assign("errors", $errors);
			$this->smarty->assign("types", $this->sys_options['ticket_types']);
			
			
			require_once 'groups.class.php';
			$groups = new Groups($this->db);
			$this->smarty->assign("departments", $groups->GetDataList());
		}
		else {
			require_once 'products.class.php';
			$products = new Products($this->db);
			$this->smarty->assign("products", $products->GetDataList());
		}
		
		return $this->smarty->fetch("ticket_form.html");
	}
	
	function showRestoreForm()
	{
		return $this->smarty->fetch("rest_form.html");
	}

	
	function validateTicket($vars, &$errors)
	{
		$errors = array();
		
		// IPs bans
		if (!empty($this->sys_options['tickets_list']['banned_ips']))
		{
			if (preg_match("/^\s*{$_SERVER['REMOTE_ADDR']}\s*$/m", $this->sys_options['tickets_list']['banned_ips'], $match))
			{
				$errors[] = 'Your IP adress has been banned.';
				return false;
			}
		}
		
		// emails bans
		if (!empty($this->sys_options['tickets_list']['banned_emails']) && !empty($vars["email"])) {
			if (preg_match("/^\s*{$vars['email']}\s*$/m", $this->sys_options['tickets_list']['banned_emails'], $match)) {
				$errors[] = 'Your email adress has been banned.';
				return false;
			}
		}

		// flood protection
		if ($this->sys_options['tickets_list']['flood_protection'])
		{
			$cnt = $this->db->getOne(
				"SELECT COUNT(*) FROM #_PREF_tickets", 
				array (
					'creator_user_ip' => $_SERVER['REMOTE_ADDR'],  
					'#PLAIN' => "created_at > (NOW() - INTERVAL ". $this->sys_options['tickets_list']['flood_protection'] ." SECOND)"
			));
			
			if ($cnt > 0) 
			{
				$errors[] = "Flood protection system: ".'please wait for '.$this->sys_options['tickets_list']['flood_protection'].' seconds';
			}
		}		
		
		
		// validation
		if (trim($vars["username"]) == "")      $errors[] = "Please enter name";
		if (trim($vars["email"]) == "")         $errors[] = "Please enter email";
		if (trim($vars["description"]) == "")   $errors[] = "Please describe problem";
//		if (trim($vars["caption"]) == "")       $errors[] = "Please give short description";
		
		//todo - check error in additional fields
		$fields = $this->getTicketFields();
		foreach ($fields as $field_id=>$options) {
			if ($options["show_in_userside"] && 0 == $options["ticket_field_is_optional"] && trim($vars["form_field"][$field_id]) == "") $errors[] = "Please enter ". $options["ticket_field_caption"];
		}
		if (empty($errors)) return true;
		else return false;
	}
	
	function saveTicket(&$vars)
	{
		$db = $this->db;
		
		if (!empty($vars['ticket_product_id'])) $this->product_id = $vars['ticket_product_id'];
		$ticket_id = $db->getNextId('#_PREF_tickets', 'ticket_id');
		
		// make unique ticket num
		$ticket_id6 = $ticket_num = Tickets::GenerateUniqId();;
		
		$vars['ticket_id']  = $ticket_id;
		$vars['ticket_id6'] = $ticket_id6;
		
		// status
		if (!empty($this->sys_options['tickets']['status_for_unanswered']))    $status = $this->sys_options['tickets']['status_for_unanswered'];
		else if (!empty($this->sys_options['tickets']['status_for_reopened'])) $status = $this->sys_options['tickets']['status_for_reopened'];
		else $status = 'Open';
		
		require_once 'products.class.php';
		$products = new Products($db);
		$product_data = $products->GetProductData($this->product_id);
		
		$ticket_data = array (
			'ticket_id'             => $ticket_id,
			'ticket_num'            => $ticket_num,
			'group_id'              => $vars["group_id"],
			'ticket_product_id'     => $this->product_id,
			'creator_user_id'       => 0,
			'creator_user_name'     => $vars["username"],
			'creator_user_ip'       => $_SERVER['REMOTE_ADDR'],
			'caption'               => @$vars["caption"],
			'ticket_email_customer' => $vars["notify_customer"],
			'type'                  => $vars["type"],
			'description'           => $vars["description"],
			'customer_name'         => $vars["username"],
			'customer_phone'        => $vars["phone"],
			'customer_email'        => $vars["email"],
			'ticket_email_customer' => !empty($vars["notify_customer"]) ? 1 : 0,
			'status'                => $status,
			'priority'              => "Normal",
			'assigned_to'           => $product_data['default_tech'] ? $product_data['default_tech'] : 0,
			'modified_at'           => 'NOW()',
			'created_at'            => 'NOW()' 
		);
		
		$this->smarty->assign("ticket_id",  $ticket_id);
		$this->smarty->assign("ticket_id6", $ticket_id6);
		$this->smarty->assign("ticket_url", $this->sys_options['common']['ticket_form_url']."?ticket_id=".$ticket_id."&email=".$vars["email"]."&button=View+ticket");

		$db->qI('#_PREF_tickets', $ticket_data);
		
		// process attachemets
		if (!empty($_FILES['file_atachment']['name'])) {
			$res = $this->processAtachment('file_atachment', $this->sys_options['attachments']['directory']);
			if (is_array($res)) {
				require_once 'ticket.class.php';
				$ticket = new Ticket($ticket_id);
				$ticket->AddMessage('', '', $res);
			}
		}
		
		// add ticket history note
		$history_data = array(
			'ticket_id' => $ticket_id,
			'user_id'   => 0,
			'his_notes' => 'Created',
			'rec_date'  => 'NOW()'
		);
		
		// insert history data
		$this->db->qI('#_PREF_tickets_history', $history_data);

		
		if (!empty($vars["form_field"]) && is_array($vars["form_field"])) 
		{
			$fields = $vars["form_field"];
			
			// add fields
			$field_data = array (
				'ticket_id'         => $ticket_id,
				'ticket_product_id' => $this->product_id
			);
			
			$db->qD('#_PREF_tickets_products_forms_values', $field_data);
			
			foreach ($fields as $field_id=>$field_value)
			{
				$field_data['ticket_field_id']    = $field_id;
				$field_data['ticket_field_value'] = $field_value;
				
				$db->qI('#_PREF_tickets_products_forms_values', $field_data);
			}
		}
		
		return true;
	
	}
	
	function processAtachment($atachment_name, $upload_dir, $allowed_extensions = array())
	{
		if (!is_dir($upload_dir)) {
			//$this->request->setAttribute('message', 'Could not find directory for uploading files. Please configure it at \'Configure -> System Options\'');
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
					//$this->request->setAttribute('message', 'There was an error storing your attachment -- please try again (try to change access rights)');
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
				// $this->request->setAttribute('message', 'There was an error storing your attachment -- The file type was rejected for security reasons.');
			}
		}
		return null;
	}
}

?>