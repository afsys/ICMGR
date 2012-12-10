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

require_once 'Classes/tickets.class.php';
require_once 'Classes/users.class.php';
require_once 'Classes/groups.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/sx_db_ini.class.php';

class TicketsAddFromLinkView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer =& $request->getAttribute('SmartyRenderer');
		$db       =& sxDb::instance();
		
		$links = $request->getParameter('links');
		if (!empty($links)) {
			// init
			__('');
			$user_id  = $user->getAttribute('user_id');
			$statuses = array();
			$links    = explode("\n", $links);
			
			// iterate links
			foreach ($links as $lnk) {
				if (preg_match('/\?ticket_id=(\d{6})&email=([\w\.@]+?)&button=View\+ticket/', $lnk, $matches)) {
					list (, $ticket_num, $customer_email) = $matches;
					$ticket_where = array('ticket_num' => $ticket_num, 'customer_email' => $customer_email);
					$creator_user_id = $db->getOne('SELECT creator_user_id FROM #_PREF_tickets', $ticket_where);
					if ($creator_user_id === null) {
						$statuses[$ticket_num] = __('Could not find ticket');
					}
					else if ($creator_user_id == $user_id) {
						$statuses[$ticket_num] = __('Already owned by you');
					}
					else if ($creator_user_id != 0) {
						$statuses[$ticket_num] = __('Already owned by other cutomer');
					}
					else if ($creator_user_id == 0) {
						$db->qI('#_PREF_tickets', array('creator_user_id' => $user_id), 'UPDATE', $ticket_where);
						$statuses[$ticket_num] = __('Successfully added');
					}
					
				}
			}
			
			$renderer->setAttribute('statuses', $statuses);
		}
		
		$renderer->setAttribute('pageBody', 'ticketsAddFromLinkAction.html');
		$renderer->setTemplate('../../index.html');
		return $renderer;
	}
}
?>