<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Live chat options action
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 04, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php';
	
class StatusImageAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		$db =& sxDb::instance();
		
		$curr_url = $request->getParameter('curr_url');
		if (!empty($curr_url)) {
			$fs_data = array (
				'fp_url'       => $curr_url,
				'fp_user_ip'   => ip2long(get_user_ip()),
				'fp_rec_date'  => 'NOW()'
			);
			
			$db->qI('#_PREF_lc_footprints', $fs_data);
		}
		
		header("Content-type: image/jpeg");
		if (count(LiveChat::GetAgentsList('available'))) {
			echo file_get_contents('images/lc_status/on.gif');
		}
		else {
			echo file_get_contents('images/lc_status/off.gif');
		}
		
		return VIEW_NONE;
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;
	}

	function handleError (&$controller, &$request, &$user)
	{
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