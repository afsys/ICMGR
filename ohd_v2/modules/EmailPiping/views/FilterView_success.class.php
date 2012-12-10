<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to add new email-piping filter.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'Classes/products.class.php';
require_once 'Classes/tickets.class.php';
require_once 'Classes/users.class.php';
require_once 'Classes/groups.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/sx_db.class.php';
//require_once 'Classes/sx_db_ini.class.php';

class FilterView extends View
{
	var $db;

	function & execute (&$controller, &$request, &$user)
	{
		$will = $request-> getParameter('will');
		
		// alias inherited data for easy access
		$renderer =& $request->getAttribute('SmartyRenderer');
		$this->db =& sxDb::instance();
		$db       =& sxDb::instance();
		
		// get products
		$products = new Products($db);
		$renderer->setAttribute('products', $products->GetDataList());
		
		// get user`s groups
		$groups = new Groups($db);
		$renderer->setAttribute('groups', $groups->GetDataList());
		

		$renderer-> setAttribute('pageBody', 'Filter.html');
		$renderer-> setAttribute('message', $request->getAttribute('message'));
		if ('edit' == $will)
		{
			$renderer->setAttribute('filter', $this->get_filter_info($request->getParameter('id')));
		}
		
        // set statuses, priorities values
        $dbIni = new sxDbIni($db);
        $prefs = $dbIni->LoadIni(DB_PREF.'sys_options');
        $renderer->setAttribute('ticket_priorities', $prefs['ticket_priorities']);
        $renderer->setAttribute('ticket_statuses',   $prefs['ticket_statuses']);
        
        // assign-to users list
        $users = new Users($db);
        $renderer->setAttribute('users', $users->GetDataList());

		$renderer->setTemplate('../../index.html');
		return $renderer;
	}

	function get_filter_info($id)
	{
		$this->db->query('SELECT * FROM #_PREF_email_filter WHERE id='.$id);
		$r = $this->db->fetchAssoc();
		return $r;
	}


}
?>