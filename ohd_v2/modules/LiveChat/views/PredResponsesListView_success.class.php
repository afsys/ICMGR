<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to show predefined responses list.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php'; 

class PredResponsesListView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer   =& $request->getAttribute('SmartyRenderer');
		$db         =& sxDb::instance();
		
		$db->q('SELECT * FROM #_PREF_lc_pred_responses ORDER BY resp_caption');
		$pred_responses = array();
		while ($item = $db->fetchAssoc()) {
			$pred_responses[] = $item;
		}
		$renderer->setAttribute('pred_responses', $pred_responses);
		
		$renderer->setAttribute('pageBody', 'pred_responses_list.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}

}
?>