<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to manage system preferences.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Mar 20, 2006
 * @version    1.00 Beta
 */

require_once 'Classes/sx_db_ini.class.php';  
require_once 'Classes/groups.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/users.class.php';
require_once ("xajax.inc.php");

class QFiltersEditView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = & $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		$dbIni =  new sxDbIni($db);

		// load system options
		$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
		$renderer->setAttribute('sys_options', $sys_options);
		
		// get groups (departments);
		$groups = new Groups($db);
		$renderer->setAttribute('groups', $groups->GetDataList());
		
		// get products
		$products = new Products();
		$renderer->setAttribute('products', $products->GetDataList());
		
		// make assign to users-array
		$users = new Users();
		
		$at_users = $users->GetDataList();
		
		array_unshift(
			$at_users,
			array (
				'user_id'   => 0,
				'user_name' => '('. __('unassigned') .')'
			),
			array (
				'user_id'   => -1,
				'user_name' => '('. __('current_user') .')'
			)
		);
		
		$renderer->setAttribute('at_users', $at_users);
		
		
		// COPY TO: OHD\modules\Manage\actions\QFiltersEditAjax.php
		
		// ajax
		include 'modules/Manage/QFiltersEdit.common.php';
		$js_code = $xajax->getJavascript('', '../js/xajax.js', null, 'js/xajax.js');
		$renderer->setAttribute('js_code', $js_code);

		// fetch array
		$renderer->setAttribute('pageBody', 'qFiltersEdit.html');
		$renderer->setTemplate('../../index.html');
		return $renderer;
	}
}
?>