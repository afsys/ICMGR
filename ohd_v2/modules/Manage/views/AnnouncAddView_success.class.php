<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to add and edit announcement.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

class AnnouncAddView extends View
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
		$db =& sxDb::instance();
		
		// edit announcement
		$ann_id = $request->getParameter('ann_id');
		if (!empty($ann_id))
		{
			$db->q('SELECT * FROM #_PREF_announcements', array('ann_id' => $ann_id));
			$ann = $db->fetchAssoc();
			$renderer->setAttribute('ann', $ann);
		}
		
		$renderer->setAttribute('pageBody', 'announcAdd.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}

}
?>