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

class ListFiltersView extends View
{

	function &execute(&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = & $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		
		// get filters list
		$db->q("
			SELECT 
			   *
			FROM 
			   #_PREF_email_filter f
			   LEFT JOIN #_PREF_tickets_products g ON g.ticket_product_id = f.ticket_product_id
			ORDER BY 
			   filter_order, g.ticket_product_id, id
		");
		$filters = array();
		while ($data = $db->fetchAssoc()) $filters[] = $data;
		
		/*echo "<pre>";
		var_dump($filters);
		echo "</pre>";/**/

		$renderer->setAttribute('filters', $filters);
		$renderer->setAttribute('pageBody', 'ListFilters.html');

		$renderer->setTemplate('../../index.html');
		return $renderer;
	}

}
?>