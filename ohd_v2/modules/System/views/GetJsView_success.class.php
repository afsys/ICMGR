<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * View for showing JS code.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Mar 22, 2006
 * @version    1.00 Beta
 */

require_once ("Classes/sx_db_ini.class.php");  

class GetJsView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		$file_name = $request->getParameter('file');

		// alias inherited data for easy access
		$renderer = &$request->getAttribute('SmartyRenderer');
		$db    =& sxDb::instance();
		$dbIni =  new sxDbIni($db);
		$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');

		$renderer->setAttribute('sys_options', $sys_options);

		$renderer->setAttribute('username', $request->getAttribute('username'));
		$renderer->setAttribute('remoteAddy', $_SERVER['REMOTE_ADDR']);
		
		switch ($file_name) {
			case 'adminMenu.js':
				header("Content-type: text/javascript");
				$is_customer = $user->getAttribute('is_customer');
				if ($is_customer) $renderer->setTemplate('../../../js/JSCookMenu/actualUserMenu.js');
				else $renderer->setTemplate('../../../js/JSCookMenu/actualMenu.js');
				break;

			case 'userMenu.js':
				header("Content-type: text/javascript");
				$renderer->setTemplate('../../../js/JSCookMenu/userMenu.js');
				break;

			case 'ticketList.css':
				header("Content-type: text/css");
				//added by leo - to remove caching of this css
				header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
				header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // always modified
				header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
				header ("Pragma: no-cache");              // HTTP/1.0

				$renderer->setTemplate('../templates/ticketsList-styles.html');
				break;
				
			case 'translations.js':
				header("Content-type: text/javascript");
				$renderer->setTemplate('../../../js/translations.js');
				break;

			default:
				echo "alert('Unknown file')";
				die();
				break;
		}


		return $renderer;
	}

  /**
  * There's no cleanup to do for this view.
  *
  function cleanup ()
  {

  }
   */
}
?>