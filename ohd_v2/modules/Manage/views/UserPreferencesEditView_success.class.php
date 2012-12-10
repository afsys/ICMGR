<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to manage user preferences.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 14, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);

require_once 'Classes/users.class.php';
require_once 'Classes/groups.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/sx_db_ini.class.php';

class UserPreferencesEditView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = &$request->getAttribute('SmartyRenderer');
		$db       =& sxDb::instance();
		$dbIni    = new sxDbIni($db);
		$users    = $users = new Users($db);
		$products = new Products($db);        
		
		$user_id  = $user->getAttribute('user_id');
		
        // get allowed languages array
        $lang_list = getLangsList();
        $renderer->setAttribute('lang_list', $lang_list);
		
		// get products list
		$products_list = $products->GetDataList();
		$renderer->setAttribute('products_list', $products_list);
		
		// get common info
		$user_data = $users->GetUserData($user_id);
		$renderer->setAttribute('user_data', $user_data);
		
		// get current prefs
		$user_options = $user->GetOptions();
		
		$renderer->setAttribute('notification_emails', $user_options['notification_emails']);
		$renderer->setAttribute('defaults', $user_options['defaults']);
		$renderer->setAttribute('user_options', $user_options);
		
		
		/*
		echo "<pre style='text-align: left;'>";
		//var_dump($user_data);
		var_dump($def_prefs);
		var_dump($user_options);
		var_dump($prefs	);
		echo "</pre>"; /**/
		$time_zones = array(
                         "12"    => "GMT+12",
                         "11"    => "GMT+11",
                         "10.5"  => "GMT+10:30",
                         "10"    => "GMT+10",
                         "9.5"   => "GMT+9:30",
                         "9"     => "GMT+9",
                         "8"     => "GMT+8",
                         "7"     => "GMT+7",
                         "6"     => "GMT+6",
                         "5"     => "GMT+5",
                         "4"     => "GMT+4",
                         "3"     => "GMT+3",
                         "2"     => "GMT+2",
                         "1"     => "GMT+1",
                         "0"     => "GMT",
                         "-1"    => "GMT-1",
                         "-2"    => "GMT-2",
                         "-3"    => "GMT-3",
                         "-4"    => "GMT-4",
                         "-5"    => "GMT-5",
                         "-6"    => "GMT-6",
                         "-7"    => "GMT-7",
                         "-8"    => "GMT-8",
                         "-9"    => "GMT-9",
                         "-10"   => "GMT-10",
                         "-11"   => "GMT-11",
                         "-12"   => "GMT-12",
                         "-13"   => "GMT-13",
                         "-14"   => "GMT-14"
                       );

		$renderer->setAttribute('time_zones',$time_zones);
		$renderer->setAttribute('pageBody', 'userPreferencesEdit.html');
		$renderer->setTemplate('../../index.html');
		return $renderer;
	}
}
?>