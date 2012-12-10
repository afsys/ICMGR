<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Show LiveChat options.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 06, 2006
 * @version    1.00 Beta
 */
 
require_once 'Classes/livechat.class.php'; 

class OptionsView extends View
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
		
		$ohd_base_url = ($_SERVER["HTTPS"] == "on" ? "https://" : "http://") . $_SERVER['HTTP_HOST'] . dirname($_SERVER["REQUEST_URI"]);
		$ohd_base_url = str_replace('\\', '/', $ohd_base_url);
		
		$renderer->setAttribute('ohd_base_url', $ohd_base_url);
		$renderer->setAttribute('pageBody', 'options.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}

}
?>