<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Show chat request user form.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php';
	
class PredResponseDeleteAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		$db =& sxDB::instance();
		$resp_id  = $request->getParameter('resp_id');
		
		if ($resp_id) $db->qD('#_PREF_lc_pred_responses', array('resp_id' => $resp_id));
		
		header('Location: index.php?module=LiveChat&action=PredResponsesList');
		exit();
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