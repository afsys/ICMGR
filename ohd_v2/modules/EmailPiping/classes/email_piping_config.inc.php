<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Class for process filters.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 10, 2006
 * @version    1.00 Beta
 */

class EmailPipingConfigList
{
	var $db;
	var $data;

	function EmailPipingConfigList()
	{
		$this->db = sxDB::instance();
		$this->RefreshConfigs();
	}

	function GetConfigs()
	{
		return $this->data;
	}

	function RefreshConfigs()
	{
		$this->data = array();
		$this->db->q('SELECT * FROM #_PREF_piping_accounts');
		while ($row = $this->db->fetchAssoc()) {
			$this->data[] = new EmailPipingConfig($row);
		}
	}
}

class EmailPipingConfig
{
	var $data;

	function EmailPipingConfig($data)
	{
		$this->data = $data;
	}
	
	function GetConfig()
	{
		return $this->data;
	}

	function IsConfigCorrect()
	{
		// TODO
		return true;
	}

	function SaveConfig($Config)
	{
		die('E:\Projects\Omni\ohd_new\modules\EmailPiping\classes\email_piping_config.inc.php : 67');
		
		if (!$this->is_proper($Config, $this->get_ticket_table_structure())) {
			// return false;
		}
		$this->db->query("DELETE FROM #_PREF_config_string WHERE name like '".EPC_PREFIX."%'");
		
		foreach ($Config as $key => $value) {
			$Data = array('name'=> EPC_PREFIX.$key, 'value'=> $value);
			$this->db->qI('#_PREF_config_string', $Data);
		}
		return true;
	}
}

?>