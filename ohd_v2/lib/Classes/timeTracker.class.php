<?php

/**
 * Tickets list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

class TimeTracker
{
	/**
	 * Database object
	 * @var sxDB
	 */
	var $db = null;
	
	var $user_id = null;
	
	var $message_id = null;
	
	/**
	 * Constructor
	 */
	function TimeTracker($user_id, $ticket_id, $message_id = null)
	{
		$this->db           =& sxDB::instance();
		$this->user_id      =  $user_id;
		$this->ticket_id    =  $ticket_id;
		$this->message_id   =  $message_id;
	}
	
	/**
	 * Add worked time value
	 * @param     string       $notes         record notes
	 * @return    integer                     tt id number
	 */
	function AddTT($notes = '')
	{
		$tt_id = $this->db->getNextId('#_PREF_tickets_time_tracking', 'tt_id');
		
		$tt_data = array (
			'tt_id'              => $tt_id,
			'ticket_id'          => $this->ticket_id,
			'ticket_message_id'  => $this->message_id,
			'tracked_by_user_id' => $this->user_id,
			'tt_notes'           => $notes,
			'tt_created'         => 'NOW()'
		);
		
		$r = $this->db->qI('#_PREF_tickets_time_tracking', $tt_data);
		
		return $tt_id;
	}
		
	/**
	 * Add worked time value
	 * @param     double       $value         worked time
	 * @param     string       $notes         record notes
	 * @return    integer                     tt id number
	 */
	function AddWorked($tt_id, $value, $notes = '')
	{
		$this->db->q("UPDATE #_PREF_tickets_time_tracking SET tt_worked = tt_worked + $value", array('tt_id' => $tt_id));
	}
	
	/**
	 * Add worked time value
	 * @param     double       $value         worked time
	 * @param     string       $notes         record notes
	 * @return    integer                     tt id number
	 */
	function AddCharged($tt_id, $value, $notes = '')
	{
		$this->db->q("UPDATE #_PREF_tickets_time_tracking SET tt_charged = tt_charged + $value", array('tt_id' => $tt_id));
	}


	/**
	 * Add billable time value
	 * @param     double       $value         billed time
	 * @param     string       $notes         record notes
	 * @return    integer                     tt id number
	 */
	function AddBilled($tt_id, $value, $notes = '')
	{
		$this->db->q("UPDATE #_PREF_tickets_time_tracking SET tt_billed = tt_billed + $value", array('tt_id' => $tt_id));
	}
	
	/**
	 * Add payable time value
	 * @param     double       $value         payed time
	 * @param     string       $notes         record notes
	 * @return    integer                     tt id number
	 */
	function AddPayed($tt_id, $value, $notes = '')
	{
		$this->db->q("UPDATE #_PREF_tickets_time_tracking SET tt_payed = tt_payed + $value", array('tt_id' => $tt_id));
	}
	
	function Delete($tt_id)
	{
		$db =& sxDB::instance();
		$db->qD('#_PREF_tickets_time_tracking', array('tt_id' => $tt_id));
	}
	
	function GetDataList()
	{
		$this->db->q("
			SELECT 
			   tt_id,
			   tt.ticket_id,
			   tt.tracked_by_user_id,
			   tt.ticket_message_id,
			   tt.tt_worked,
			   tt.tt_charged,
			   tt.tt_billed,
			   tt.tt_payed,
			   tt.tt_notes,
			   tt.tt_created,
			   CONCAT(u.user_name, ' ', u.user_lastname) AS tracked_by_user_name
			FROM
			   #_PREF_tickets_time_tracking tt
			   INNER JOIN #_PREF_users u ON u.user_id = tt.tracked_by_user_id
			WHERE
			   ticket_id = {$this->ticket_id}
			ORDER BY 
			   tt_created DESC
		");
		
		$tt_items = array();
		while ($tt_item = $this->db->fetchAssoc()) $tt_items[] = $tt_item;
		return $tt_items;
	}
	
	function GetOneData()
	{

		

		
		return $tt_id;
	}	

}