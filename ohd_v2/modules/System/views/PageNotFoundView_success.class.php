<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * View for showing 404 error page.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jul 20, 2006
 * @version    1.00 Beta
 */

class PageNotFoundView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = & $request->getAttribute('SmartyRenderer');
		
		$m = $request->getParameter('module');
		$a = $request->getParameter('action');
		
		
		// genarate module
		$generate_module = $request->getParameter('generate_module');
		if (defined('ALLOW_AUTO_MODULE_GENERATION') && $generate_module) {
			list($module_name, $action_name) = explode('.', $generate_module);
			
			// generate module directory if it does not exists
			if (file_exists("./Modules/$module_name")) {
				if (!is_dir("./Modules/$module_name")) {
					die('ohd\trunk\modules\System\views\PageNotFoundView_success.class.php : 39');
				}
			}
			else {
				mkdir("./Modules/$module_name");
			}
			if (file_exists("./Modules/$module_name/actions")) {
				if (!is_dir("./Modules/$module_name/actions")) {
					die('ohd\trunk\modules\System\views\PageNotFoundView_success.class.php : 51');
				}
			}
			else {
				mkdir("./Modules/$module_name/actions");
			}
			if (file_exists("./Modules/$module_name/templates")) {
				if (!is_dir("./Modules/$module_name/templates")) {
					die('ohd\trunk\modules\System\views\PageNotFoundView_success.class.php : 59');
				}
			}
			else {
				mkdir("./Modules/$module_name/templates");
			}
			if (file_exists("./Modules/$module_name/views")) {
				if (!is_dir("./Modules/$module_name/views")) {
					die('ohd\trunk\modules\System\views\PageNotFoundView_success.class.php : 67');
				}
			}
			else {
				mkdir("./Modules/$module_name/views");
			}
			
			// GENERATE VARIABLES
			$vars = array (
				'{$action_name}'   => $action_name,
				'{$creation_date}' => date("M d, Y"),
				'{$desc}'          => '..'
			);
			
			// MAKE TEMPLATE
			$tpl_filename = $request->getParameter('template_name');
			if ($tpl_filename) {
				$tpl_filename .= ".html";
				$handle = fopen("./Modules/$module_name/templates/{$tpl_filename}", "w");
				fwrite($handle, "---");
				fclose($handle);
				
				$vars['{$tpl_filename}'] = $tpl_filename;
			}
			
			// MAKE ACTION-CLASS FILE
			if (!file_exists("./Modules/$module_name/actions/{$action_name}Action.class.php")) {
				$cnt = file_get_contents('./Modules/System/templates/pageNotFound_action_tpl.html');
				$cnt = strtr($cnt, $vars);
				$handle = fopen("./Modules/$module_name/actions/{$action_name}Action.class.php", "w");
				fwrite($handle, $cnt);
				fclose($handle);
			}
			
			// MAKE VIEW-CLASS FILE
			if (!file_exists("./Modules/$module_name/views/{$action_name}View_success.class.php")) {
				$cnt = file_get_contents('./Modules/System/templates/pageNotFound_view_tpl.html');
				$cnt = strtr($cnt, $vars);
				$handle = fopen("./Modules/$module_name/views/{$action_name}View_success.class.php", "w");
				fwrite($handle, $cnt);
				fclose($handle);
			}
			
			
			header("Location: index.php?module=$module_name&action=$action_name");
		}

		
		$renderer->setAttribute('module', $m);
		$renderer->setAttribute('action', $a);
		
		$renderer->setAttribute('pageBody', 'pageNotFound.html');
		$renderer->setTemplate('../../index.html');

		return $renderer;
	}
}
?>
