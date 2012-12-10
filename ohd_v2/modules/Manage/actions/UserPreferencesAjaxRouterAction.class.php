<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Ajax router for User preferences.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Aug 31, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/sx_db_ini.class.php';  
 
class UserPreferencesAjaxRouterAction extends AjaxAction
{
	function execute (&$controller, &$request, &$user)
	{
		parent::execute($controller, $request, $user);
		
		
		return VIEW_NONE;
	}
	
	function switchLiveChat($params)
	{
		$dbIni    = new sxDbIni();
		$user_id  = $this->user->getAttribute('user_id');
		
		// update LC option
		$enable_value = $params->enable ? '1' : '0';
		$user_options  = array('defaults' => array('enable_livechat' => ($enable_value)));
		$dbIni->saveIni(DB_PREF.'users_options', $user_options, array('user_id' => $user_id));
		
		// update last action time value
		$this->db->qI(
			'#_PREF_users',
			array(
				'actual_time' => ($params->enable ? 'NOW()' : 'NULL'),
				'lc_enabled' => $enable_value
			), 
			'UPDATE', 
			array('user_id' => $user_id)
		);
	}


	function isSecure()
	{
		return false;   
	}

}

?>