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
 
/*

Also here is list of URL operations:
/ohd/index.php?module=Tickets&action=TicketsManage&ticketNum=XXX&email=XXX&do=open
/ohd/index.php?module=Tickets&action=TicketsManage&ticketNum=XXX&email=XXX&do=close
/ohd/index.php?module=Tickets&action=TicketsManage&ticketNum=XXX&email=XXX&do=expireat&date=DDMMYYYY
/ohd/index.php?module=Tickets&action=TicketsManage&ticketNum=XXX&email=XXX&do=setstatus&date=STATUS_VALUE
/ohd/index.php?module=Tickets&action=TicketsManage&ticketNum=XXX&email=XXX&do=setpriority&date=PRIORITY_VALUE

http://ohd/index.php?module=Tickets&action=TicketsManage&do=create&ticketNum=AAA&product=OSS&caption=CaptionTest&desc=What%20is%20the%20problem%20with%20my%20URL%20Create%201&name=Don&phone=555-5555&email=slyder@homa.net.ua&add_emails=&status=new&priority=low&expire_at=07202006&email_to_customer=1

When I view the ticket it is CLOSED.

*/ 

error_reporting(E_ALL);
require_once 'mail.class.php';
require_once 'Classes/ticket.class.php';
require_once 'Classes/sx_db_ini.class.php';
	
class TicketsManageAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// is_authenticated
		$is_authenticated = $user->isAuthenticated();
		
		// make aliases
		$db = $this->db    =& sxDb::instance();
		$dbIni             =  new sxDbIni($db);
		$this->sys_options = $sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
		
		$user_options  =  $user->GetOptions();
		$this->request =& $request;

		$action = strtolower($request->getParameter('do'));
		if ($action == 'create') {
			return $this->processTicketCreation();
		}

		$user_id      = $user->getAttribute('user_id');
		$ticket_num   = $request->getParameter('ticketNum');
		$ticket_email = $request->getParameter('email');
		
		$ticket_id = $db->getOne('SELECT ticket_id FROM #_PREF_tickets', array('ticket_num' => $ticket_num, 'customer_email' => $ticket_email));
		if ($ticket_id) {
			$ticket = new Ticket($ticket_id);
			
			if ($action) {
				$action = strtolower($action);
				switch ($action) {
					case 'open':
						$type  = 'status';
						$value = !empty($sys_options['tickets']['status_for_opened']) ? $sys_options['tickets']['status_for_opened'] : 'Open';
						break;

					case 'close':
						$type  = 'status';
						$value = !empty($sys_options['tickets']['status_for_closed']) ? $sys_options['tickets']['status_for_closed'] : 'Closed';
						break;
						
					case 'setstatus':
						$type  = 'status';
						$value = $this->getStatusName();
						break;

					case 'setpriority':
						$type  = 'priority';
						$value = $this->getPriorityName();
						break;
						
					case 'expireat':
						$type  = 'expired_at';
						$value = $this->getExpiredAtValue('date');
						break;
					
				}
			}
			
			if (!empty($type) && isset($value)) {
				$res = $ticket->UpdateProperty($user_id, $type, $value);
				echo "Changed $type to $value...";
				
			}
			else {
				return $this->showError('Action could not be performed...');
			}
		}
		else {
			return $this->showError('message', 'Could not find ticket with same ticket number and customer email...');
		}
		
		return VIEW_SUCCESS;
	}
	
	function processTicketCreation() 
	{
		$request =& $this->request;
		
		if ($this->db->getOne('SELECT COUNT(*) FROM #_PREF_tickets', array('ticket_num' => $request->getParameter('ticketNum')))) {
			return $this->showError('Ticket with same number already exists...');
		}
		
		$product_caption = $request->getParameter('product');
		$ticket_product_id = $this->db->getOne(
			'SELECT ticket_product_id FROM #_PREF_tickets_products', 
			array('ticket_product_caption' => $product_caption)
		);
		
		if (empty($ticket_product_id)) {
			return $this->showError('Could not find defined product...');
		}
		
		if (!$request->getParameter('ticketNum')) {
			return $this->showError('Ticket number do not defined...');
		}

		if (!$request->getParameter('caption')) {
			return $this->showError('Ticket caption do not defined...');
		}
		
		if ($request->getParameter('owner')) {
			$r = $this->db->getOne('SELECT user_id FROM #_PREF_users', 
				array('user_lastname' => $request->getParameter('owner'), 'is_customer' => 0));
			$assigned_to = is_numeric($r) ? $r : 0;
		}
		else $assigned_to = 0;


		$ticket_id = $this->db->getNextId('#_PREF_tickets', 'ticket_id');
		
		$ticket_data = array (
			'ticket_id'             => $ticket_id,
			'ticket_num'            => $request->getParameter('ticketNum'),
			//'group_id'              => $request->getParameter('group_id'),
			'ticket_product_id'     => $ticket_product_id,
			//'ticket_product_ver'    => $request->getParameter('ticket_product_ver'),
			'caption'               => $request->getParameter('caption') ? $request->getParameter('caption') : '',
			'description'           => $request->getParameter('desc') ? $request->getParameter('desc') : '',
			'description_is_html'   => 0,
			'customer_name'         => $request->getParameter('name'),
			'customer_phone'        => $request->getParameter('phone') ? $request->getParameter('phone') : '',
			'customer_email'        => $request->getParameter('email'),
			'customer_add_emails'   => $request->getParameter('add_emails'),
			'status'                => $this->getStatusName() ? $this->getStatusName() : '',
			'priority'              => $this->getPriorityName() ? $this->getPriorityName() : '',
			'type'                  => '',
			'expired_at'            => $this->getExpiredAtValue('expire_at'),
			'assigned_to'           => $assigned_to,
			'due_date'              => $request->getParameter('due_date'),
			'ticket_email_customer' => $request->getParameter('email_to_customer') ? 1 : 0,
			'modified_at'           => 'NOW()',
			'created_at'            => 'NOW()'
		); 
			
		/*echo "<pre>";
		var_dump($ticket_data);
		die('a'); /**/
			
		$this->db->qI('#_PREF_tickets', $ticket_data);
		
		// ticket history
		$history_notes = 'Created';
		$history_data = array(
			'ticket_id' => $ticket_id,
			'user_id'   => 0,
			'his_notes' => $history_notes,
			'rec_date'  => 'NOW()'
		);
		$this->db->qI('#_PREF_tickets_history', $history_data);
		
		// NOTIFICATION EMAIL
		
		
		
		// ....
		$request->setParameter('message', 'Ticket created...');
		
		if (!empty($this->sys_options['common']['url_after_ticket_created_by_url'])) {
			header('Location: '. $this->sys_options['common']['url_after_ticket_created_by_url']);
		}
		else {
			header('Location: '. $this->sys_options['common']['ticket_form_url'] . 
			       '?ticket_id='.$ticket_data['ticket_num'].'&email='.
			       $ticket_data['customer_email'].'&button=View+ticket');
		}
		return true;
	}
	
	function getPriorityName() {
		$request =& $this->request;
		$priority = strtolower($request->getParameter('priority'));
		$value = null;
		foreach ($this->sys_options['ticket_priorities'] as $pr=>$v) {
			if (strtolower($pr) == $priority) {
				$value = $pr;
				break;
			}
		}
		return $value;
	}
	
	function getStatusName($value) {
		$request =& $this->request;
		$status = strtolower($request->getParameter('status'));
		$value = null;
		foreach ($this->sys_options['ticket_statuses'] as $pr=>$v) {
			if (strtolower($pr) == $status) {
				$value = $pr;
				break;
			}
		}
		return $value;
	}
	
	function getExpiredAtValue($varname) {
		$request =& $this->request;
		$value = null;
		$d = $request->getParameter($varname);
		if ($d && preg_match('/(^(\d\d)(\d\d)(\d\d\d\d)$)/', $d, $match)) {
			$value = $d ? "{$match[4]}-{$match[2]}-{$match[3]}" : 'NULL';
		}
		return $value;
	}

	function showError($s) {
		if (!empty($this->sys_options['common']['url_after_ticket_if_error'])) {
			header('Location: '. $this->sys_options['common']['url_after_ticket_if_error']);
			return VIEW_NONE;
		}
		else {
			$this->request->setParameter('message', $s);
			return VIEW_SUCCESS;
		}
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