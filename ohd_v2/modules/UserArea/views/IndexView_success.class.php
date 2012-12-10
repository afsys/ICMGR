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

class IndexView extends View
{
	
	function &execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer   =& $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();

		// get anns
		$anns = array();
		$db->q('SELECT * FROM #_PREF_announcements ORDER BY ann_date DESC LIMIT 3 ');
		while ($ann = $db->fetchAssoc()) 
		{
			$ann['ann_caption'] = ucfirst($ann['ann_caption']);
			$ann['ann_short']   = ucfirst($ann['ann_short']);
			$anns[] = $ann;
		}
		$renderer->setAttribute('anns', $anns);
		
		// 
		$ann_id = $request->getParameter('ann_id');
		if ($ann_id) {
			foreach ($anns as $ann) {
				if ($ann['ann_id'] == $ann_id) {
					$renderer->setAttribute('ann_full', $ann);
					break;
				}
				
			}
		}

		// fetch templates
		$renderer->setAttribute('pageBody', 'index.html');
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