<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * View for updating files
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Mar 7, 2005
 * @version    1.00 Beta
 */

require_once 'Classes/sx_updater.class.php';
require_once 'xajax.inc.php';

class UpdateFilesView extends View
{
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer   =& $request->getAttribute('SmartyRenderer');
		$db         =& sxDb::instance();
		
		require_once 'modules/Updates/update_common.php';
		$js_code = $xajax->getJavascript('', '../js/xajax.js', null, 'js/xajax.js');
		$renderer->setAttribute('js_code', $js_code);
		
		// get update files
		$install_version = $db->getOne("SELECT option_value FROM #_PREF_sys_options WHERE option_group = 'system' AND option_name = 'install_version'");
		if (!is_numeric($install_version)) $install_version = 240;
		$install_version = 240;
		
		$updater = new sxUpdater();
		$updater->SetCurrentRevision($install_version);
		$updates = $updater->GetUpdatesList(BASE_DIR."install/updates/");
		
		$renderer->setAttribute('updates', $updates);
		/* echo "<pre style='text-align: left;'>";
		var_dump($updates);
		echo "</pre>"; /**/

		$renderer->setAttribute('pageBody', 'updateFiles.html');
		$renderer->setTemplate('../../index.html');

		return $renderer;
	}

}
?>