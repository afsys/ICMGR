<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Show list of users query.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php'; 

class ShowUserFootPrintsView extends View
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
		
		$db->q('SELECT * FROM #_PREF_lc_footprints');
		$footprints = array();
		while ($data = $db->fetchAssoc()) {
			$footprints[] = $data;
		}
		$renderer->setAttribute('footprints', $footprints);
				
		$renderer->setAttribute('pageBody', 'user_footprints.html');
		$renderer->setTemplate('../index.html');
		
		return $renderer;
	}

}
?>