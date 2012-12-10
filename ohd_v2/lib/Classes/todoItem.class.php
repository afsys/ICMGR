<?php

/**
 * ToDo Item
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Apr 26, 2006
 * @version    1.00 Beta
 */
 
require_once 'todoList.class.php';
require_once 'sx_db_ini.class.php';

class ToDoItem
{
	/**
	 * Database object
	 * @var sxDB
	 */
	var $db = null;
	
	/**
	 * Data
	 * @var array
	 */
	var $data = null;
	
	/**
	 * Systems options cashe.
	 * @var array
	 */
	var $sys_options = null;
	
	
	function ToDoItem($data)
	{
		$this->db =& sxDB::instance();
		
		// tdi_id
		if (is_numeric($data)) {
			$todo_list  = new TodoList();
			$this->data = $todo_list->GetItemData($data);
		}
		// ticket_data
		else {
			$this->data = $data;
		}
	}
	
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
		if (empty($this->data['tdi_id'])) die('E:\Projects\Omni\ohd_new\lib\Classes\ticket.class.php: 172');
		$item_data = array('tdi_id' => $this->data['tdi_id']);
		$this->db->qD('#_PREF_todo_items', $item_data);
	}
	
	
	/**
	 * Returns array of system options
	 */
	function GetSysOptions()
	{
		if (is_array($this->sys_options)) return $this->sys_options;
		$dbIni = new sxDbIni($this->db);        
		return $this->sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
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
	
	function Save($data = null) {
		if ($data === null) $data =& $this->data;
		$db =& sxDB::instance();
		
		$data['updated_at'] = 'NOW()';
		if (!empty($data['tdi_id']) && (int)$data['tdi_id'] > 0) {
			$db->qI('#_PREF_todo_items', $data, 'UPDATE', array('tdi_id' => $data['tdi_id']));
			return $data['tdi_id'];
		} 
		else {
			$data['created_at'] = 'NOW()';
			$db->qI('#_PREF_todo_items', $data);
			return $db->lastInsertId();
		}
		
		
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

	
	function UpdateProperty($user_id, $pr_name, $pr_value)
	{
		if (empty($this->data['tdi_id'])) die('E:\Projects\Omni\ohd_new\lib\Classes\ticket.class.php: 260');
		
		// check value
		//if ($this->data[$pr_name] == $pr_value) return 'Ok!';
		$this->data[$pr_name] = $pr_value;
		
		// update property
		$this->db->qI('#_PREF_todo_items', array($pr_name => $pr_value), 'UPDATE', array('tdi_id' => $this->data['tdi_id']));

		$sys_options = $this->getSysOptions();
		
		// close ticket
		if ($pr_name == 'status' && $pr_value == $sys_options['tickets']['status_for_closed'])
		{
			// update closed_at date
			$this->db->qI('#_PREF_todo_items', array('closed_at' => 'NOW()'), 'UPDATE', array('tdi_id' => $this->data['tdi_id']));
			
			// doesn't update status - and do not set status for new ticket
			//$this->SetUpdatedNow($user_id, false);
		}
		// other statuses
		else
		{
			if ($pr_name == 'status' && 
				($pr_value == $sys_options['tickets']['status_for_new'] || $pr_value == $sys_options['tickets']['status_for_reopened']))
			{
				// update closed_at date
				$this->db->qI('#_PREF_todo_items', array('closed_at' => 'NULL'), 'UPDATE', array('tdi_id' => $this->data['tdi_id']));
				
			}
			
			//$this->SetUpdatedNow($user_id, false);
		}
		
		//if ($send_email && !$send_result) return 'Property Saved. <span style="color: red;">But problems with sending notification email.</span>';
		return 'Saved Ok!';
	}
	
	
}