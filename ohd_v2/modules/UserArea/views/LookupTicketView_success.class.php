<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to add user.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'Classes/tickets.class.php';
require_once 'Classes/users.class.php';
require_once 'Classes/groups.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/sx_db_ini.class.php';

class LookupTicketView extends View
{
	
	function &execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer   =& $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		$oFCKeditor =& $request->getAttribute('FCKeditor');
		$products = new Products($db);
		$tickets  = new Tickets($db);
		
		$renderer->setAttribute('pageBody', 'lookupTicket.html');
		$renderer->setTemplate('../../user_index.html');
		
		return $renderer;
	}

	/**
	* There's no cleanup to do for this view.
	*
	function cleanup ()
	{

	} */
}
?>