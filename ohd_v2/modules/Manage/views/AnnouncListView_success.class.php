<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to show announcements list.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

class AnnouncListView extends View
{
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = & $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
	
		// get anns
		$anns = array();
		$db->q('SELECT * FROM #_PREF_announcements');
		while ($ann = $db->fetchAssoc()) 
		{
			$ann['ann_caption'] = ucfirst($ann['ann_caption']);
			$ann['ann_short']   = ucfirst($ann['ann_short']);
			$anns[] = $ann;
		}
		$renderer->setAttribute('anns', $anns);

		// fetch templates
		$renderer->setAttribute('pageBody', 'announcList.html');
		$renderer->setTemplate('../../index.html');
		return $renderer;
	}
}
?>