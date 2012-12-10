<?php
  
/**
 * Tickets list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

class CannedEmails
{
	/**
	 * Database object
	 * @var sxDB
	 */
	var $db = null;

	function CannedEmails()
	{
		$this->db =& sxDB::instance();
	}

	/**
	 * Return canned emails categories array.
	 * @return    array                    canned emails categories array
	 */
	function GetCategories()
	{
		$items = array();
		$this->db->q("SELECT * FROM #_PREF_canned_emails_categories");
		while ($item = $this->db->fetchAssoc()) $items[] = $item;
		return $items;
	}

	/**
	 * Return canned emails categories array.
	 * @return    array                    canned emails categories array
	 */
	function GetItems($params = null)
	{
		$items = array();
		$this->db->q("SELECT * FROM #_PREF_canned_emails", $params);
		while ($item = $this->db->fetchAssoc()) $items[] = $item;
		return $items;
	}

}