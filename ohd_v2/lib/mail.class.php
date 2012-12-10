<?
require_once "PEAR/Mail/mime.php";
require_once "PEAR/Mail.php";
require_once "Classes/sx_db.class.php";
require_once "Classes/sx_db_ini.class.php";

class OhdMail
{
	var $sys_options = null;
	var $oPearMail;
	var $htmlMessage;
	var $subject;
	var $variables = array();

	function OhdMail()
	{
		$this->db =& sxDB::instance();
		$dbIni =  new sxDbIni($this->db);
		$this->sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
		
		$def_prefs = array (
			'common' => array (
				'em_method'      => 'direct',
				'em_enable_logs' => 1,
				'mail_grouping'  => 0
			)
		);
		$this->sys_options = $dbIni->imposeArray($def_prefs, $this->sys_options);
		
		
		switch ($this->sys_options['common']['em_method']) 
		{
			case 'sys_mail':
				$driver = "mail";
				$params = array();
				break;
				
			case 1:
				die('E:\Projects\Omni\ohd_new\lib\mail.class.php: 28');
				$driver = "smtp";
				$params = unserialize($this->config["em_smtp_config"]);
				break;
				
			case 2:
				die('E:\Projects\Omni\ohd_new\lib\mail.class.php: 34');
				$driver = "sendmail";
				$params = unserialize($this->config["em_sendmail_config"]);
				break;
				
			case 'direct':
				$driver = "smtpmx";
				$win = preg_match('/^Windows/', php_uname());
				$params = array (
					'mailname' => $_SERVER["HTTP_HOST"],
					'netdns'   => $win,
					'debug'    => false,
					'test'     => false
				);
				break;
				
			default:
				$driver = "mail";
				$params = array();
				break;
		}
		
		$this->oPearMail =& Mail::factory($driver, $params);
		return true;
	}
	
	function _AddImages(&$mime)
	{
		preg_match_all("/<img[^>]*src\s*=['\"\s]?([^\"\s>']+)/i", $this->htmlMessage,$arr);
		$matches = $arr[1];
		$aurl = parse_url($this->config["url"]);
		foreach ($matches as $k=>$v) {
			$image_name = str_replace(array($aurl["scheme"]."://".$aurl["host"], $aurl["path"]), array("",""), $v);
			$mime->addHTMLImage(BASE_DIR."/".$image_name);
			$this->htmlMessage = str_replace($v,basename($image_name),$this->htmlMessage);
		}
	}
	
	function IsPlainText($text) 
	{
		if (preg_match('/<div .+?>|<br>|<br \/>|<p>|<span .+?>/', $text)) {
			return false;
		}
		else {
			//echo "<pre><xmp>$text";
			preg_match_all("/<.+?>/", $text, $match);
			//var_dump($match);
			if (count($match[0]) < 3) return true;
			else return false;
			//die();
			
		}
		return true;
	}
	
	function SendEx($to, $subject, $message, $params = array()) 
	{
		// SEND EMAIL
		if (!$this->sys_options['common']['mail_grouping']) {
			$this->SendSimple($to, $subject, $message, $params);
			return;
		}
		
		// ADD MESSAGE TO QUERY
		
		if (isset($params['from'])) {
			$from = $params['from'];
			unset($params['from']);
		}
		else $from = null;
		
		if (isset($params['allow_grouping'])) {
			$allow_grouping = $params['allow_grouping'];
			unset($params['allow_grouping']);
		}
		else $allow_grouping = 1;
		

		//echo "<pre><xmp>";
		// parse $to and extract all emails
		if (preg_match_all('/(\b[\w\s]*)([<\[]|)\b([-a-z0-9_.]+@[-a-z0-9_.]+)([>\]]|)/', $to, $match)) {
			//var_dump($match);
			
			foreach ($match[0] as $k=>$full_email) {
				// add email into query
				$email_data = array (
					'email_to'       => $match[3][$k],
					'send_to'        => $full_email,
					'subject'        => $subject,
					'message'        => $message,
					'send_from'      => $from,
					'allow_grouping' => $allow_grouping,
					'backtrace'      => serialize(debug_backtrace())
				);
				
				/* echo "<pre style='text-align: left'>";
				var_dump($email_data['backtrace']);
				echo "</pre>"; /**/
				$this->db->qI('#_PREF_tickets_emails_query', $email_data);
				
			}
			return true;
		}
		return false;
	}
	
	function SendSimple($to, $subject, $message, $params = null) 
	{
		// set default from value
		if ($params === null || empty($params['from'])) {
			$from_name  = $this->sys_options["common"]["company_name"];
			$from_email = $this->sys_options["common"]["admin_email"];
		}
		else {
			preg_match('/(\b[\w\s]*)([<\[]|)\b([-a-z0-9_.]+@[-a-z0-9_.]+)([>\]]|)/', $params['from'], $match);
		}
		
		// apply variables
		$subject = strtr($subject, $this->variables);
		$message = strtr($message, $this->variables);
		
		// body
		$crlf = "\r\n";
		$mime = new Mail_mime($crlf);
		if (!$this->isPlainText($message)) {
			$this->htmlMessage = $message;
			$mime->setHTMLBody($this->htmlMessage);
		}
		else {
			$mime->setTXTBody($message);
		}
		$body = $mime->get();
		
		// header
		$hdrs = array (
			'From'    => $from_name . " <".$from_email.">",
			'Subject' => $subject
		);
		$hdrs = $mime->headers($hdrs);
		
		list ($name, $host) = explode("@", $from_email);
		$message_id = $name.".".date("YmdHis")."@".$host;

		$hdrs["Reply-to"]          = $from_email;
		$hdrs["X-Priority"]        = "3 (Normal)";
		$hdrs["Message-ID"]        = "<".$message_id.">";
		$hdrs["Return-path"]       = $from_email;
		$hdrs["X-Mailer"]          = "OHD Site v1.1";
		$hdrs["X-MSMail-Priority"] = "Normal";
		
		// send 
		$pear_mailer =& $this->oPearMail;
		$result = $pear_mailer->send($to, $hdrs, $body);
		
		// write log
		//if ($this->sys_options['common']['em_enable_logs'] == 1)
		{
			$props["mail_from_name"]  = $from_name;
			$props["mail_to_name"]    = !empty($to_name) ? $to_name : '';
			$props["mail_from_email"] = $from_email;
			$props["mail_to_email"]   = $to;

			$props["mail_send_time"] = date("Y-m-d H:i:s");
			$props["mail_subject"]   = $subject;
			
			$props["backtrace"]      = serialize(debug_backtrace());
			
			
			if (!PEAR::isError($result)) {
				$props["mail_send_result"] = "success";
			}
			else {
				$props["mail_send_result"] = $result->getMessage();
				if ($this->sys_options['common']['em_method'] == 3 && !empty($pear_mailer->_smtp->debug_log)) $props["mail_debug_msg"]   = $pear_mailer->_smtp->debug_log;
				else $props["mail_debug_msg"] = " ";
			}
			
			$table_pref = $this->db->table_pref;
			$this->db->table_pref = null;
			$this->db->qI(DB_PREF.'emails_log', $props);
			$this->db->table_pref = $table_pref;
			
			/* 
			$id = $this->db->lastInsertId();
			$bk = $this->db->getOne('SELECT backtrace FROM #_PREF_emails_log', array('id' => $id));
			echo($bk);
			var_dump(unserialize($bk)); /**/
		}
		
		return $result;
	}
	
	function Send($to, $subject, $text_message, $html_message = null, $to_name = "", $from_name = null, $from_email = null) 
	{
		$crlf = "\r\n";
		if ($from_name == false)  $from_name  = $this->sys_options["common"]["company_name"];
		if ($from_email == false) $from_email = $this->sys_options["common"]["admin_email"];
    
		$hdrs = array (
			'From'    => $from_name . " <".$from_email.">",
			'Subject' => $subject
		);
		
			

		$mime = new Mail_mime($crlf);
		if ($text_message !== null) $mime->setTXTBody($text_message);
		if ($html_message !== null) {
			$this->htmlMessage = $html_message;
			//$this->_AddImages($mime);
			$mime->setHTMLBody($this->htmlMessage);
		}
	  
		$body = $mime->get();
		$hdrs = $mime->headers($hdrs);

		list ($name, $host) = explode("@", $from_email);
		$message_id = $name.".".date("YmdHis")."@".$host;

		$hdrs["Reply-to"]          = $from_email;
		$hdrs["X-Priority"]        = "3 (Normal)";
		$hdrs["Message-ID"]        = "<".$message_id.">";
		$hdrs["Return-path"]       = $from_email;
		$hdrs["X-Mailer"]          = "Omni Site Secure v5.1";
		$hdrs["X-MSMail-Priority"] = "Normal";

		$pear_mailer =& $this->oPearMail;
		$result = $pear_mailer->send($to, $hdrs, $body);
    
		if ($this->sys_options['common']['em_enable_logs'] == 1)
		{
			//write log
			$props["mail_from_name"]  = $from_name;
			$props["mail_to_name"]    = $to_name ? $to_name : '';
			$props["mail_from_email"] = $from_email;
			$props["mail_to_email"]   = $to;

			$props["mail_send_time"] = date("Y-m-d H:i:s");
			$props["mail_subject"]   = $subject;
			if (!PEAR::isError($result)) {
				$props["mail_send_result"] = "success";
			}
			else {
				$props["mail_send_result"] = $result->getMessage();
				if ($this->sys_options['common']['em_method'] == 3 && !empty($pear_mailer->_smtp->debug_log))
				{
					$props["mail_debug_msg"]   = $pear_mailer->_smtp->debug_log;
				}
				else
				{
				$props["mail_debug_msg"] = " ";
				}
			}
			
			$this->db->qI('#_PREF_emails_log', $props);
		
		}
		
		return $result;
	}
	
	function AddVariables($vars) 
	{
		foreach ($vars as $k=>$v) {
			$this->variables['{$'. $k .'}'] = $v;
		}
	}
}
?>