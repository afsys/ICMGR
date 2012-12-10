<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to edit predefined response.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php'; 
require_once 'Classes/sx_db_ini.class.php'; 

class PredResponsesEditView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer           =& $request->getAttribute('SmartyRenderer');
		$db                 =& sxDb::instance();
		
		$resp_id   = $request->getParameter('resp_id');
		if ($resp_id) {
			$db->q('SELECT * FROM #_PREF_lc_pred_responses', array('resp_id' => $resp_id));
			$resp_data = $db->fetchAssoc();
			$renderer->setAttribute('resp_id',   $resp_id);
			$renderer->setAttribute('resp_data', $resp_data);
		}
		
		$renderer->setAttribute('pageBody', 'pred_response_edit.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}

}
?>