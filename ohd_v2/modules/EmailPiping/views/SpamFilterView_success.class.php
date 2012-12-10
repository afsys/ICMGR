<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to view filters list.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Mar 22, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/sx_db_ini.class.php';

class SpamFilterView extends View
{
	function &execute(&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = & $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		$dbIni = new sxDbIni($db);
		$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
		

		$renderer->setAttribute('sys_options', $sys_options);
		$renderer->setAttribute('pageBody', 'SpamFilter.html');

		$renderer->setTemplate('../../index.html');
		return $renderer;
	}

}
?>