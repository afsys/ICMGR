<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to ...
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Aug 15, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/kb.class.php';

function cmpExpiredItemsView($a, $b) {
    if ($a['expired_in_days'] == $b['expired_in_days']) return 0;
    return ($a['expired_in_days'] < $b['expired_in_days']) ? -1 : 1;
}


class ExpiredItemsView extends View
{
	function & execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db       =& sxDb::instance();
		$renderer =& $request->getAttribute('SmartyRenderer');
		
		$is_customer = $user->getAttribute('is_customer') || !$user->isAuthenticated();
		$user_right  = $user->getAttribute('user_rights');
		$is_admin    = ($user_right && SR_KB_MANAGE_CATS) == SR_KB_MANAGE_CATS;
		$renderer->setAttribute('is_admin', $is_admin);     
		
		$param  = $request->getParameter('param');
		
		$kb = new KB();
		$items = $kb->GetItems(array('#PLAIN' => 'expiration_date < (NOW() + INTERVAL 7 DAY)'));
		usort ($items, "cmpExpiredItemsView");
		$renderer->setAttribute('items',  $items);
		
		$renderer->setAttribute('root_page', 'ExpiredItems');
		$renderer->setAttribute('pageBody', 'expiredItems.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}
}

?>
