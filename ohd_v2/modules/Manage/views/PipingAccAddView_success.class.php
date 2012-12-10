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

class PipingAccAddView extends View
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
		
		// edit accounts
		$acc_id = $request->getParameter('acc_id');
		if (!empty($acc_id)) {
			$db->q('SELECT * FROM #_PREF_piping_accounts', array('acc_id' => $acc_id));
			$acc = $db->fetchAssoc();
			$renderer->setAttribute('acc', $acc);
		}

		
		$renderer->setAttribute('pageBody', 'pipingAccAdd.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}

}
?>