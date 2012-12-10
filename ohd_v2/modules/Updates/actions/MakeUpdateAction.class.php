<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Action for updating files
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Mar 7, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);

require_once 'Classes/sx_db_ini.class.php';
require_once ("xajax.inc.php");

class MakeUpdateAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{  
		$db =& sxDb::instance();
		$dbIni  =  new sxDbIni($db);

		
		// send them to the login form
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
}

?>