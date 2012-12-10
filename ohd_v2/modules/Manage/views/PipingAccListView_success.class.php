<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to show piping accounts list.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Feb 15, 2006
 * @version    1.00 Beta
 */

class PipingAccListView extends View
{
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = & $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
	
		// get accs
		$accs = array();
		$db->q('SELECT * FROM #_PREF_piping_accounts');
		while ($acc = $db->fetchAssoc()) 
		{
			$acc['acc_caption'] = ucfirst($acc['acc_caption']);
			$accs[] = $acc;
		}
		$renderer->setAttribute('accs', $accs);

		// fetch templates
		$renderer->setAttribute('pageBody', 'pipingAccList.html');
		$renderer->setTemplate('../../index.html');
		return $renderer;
	}
}
?>