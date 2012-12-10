<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Mojavi package.                                  |
// | Copyright (c) 2003 Sean Kerr.                                             |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

// @author Joo, admin@kraslex.ru
// @version $Id:4

class SmartyFilter extends Filter
{
	function execute (&$filterChain, &$controller, &$request, &$user)
	{
		// see if renderer is already loaded
		$loaded =& $request->getAttribute('SmartyRenderer');

		if ($loaded == NULL)
		{
			// smarty init params
			$params =  array(
								'cache_dir'       => SMARTY_CACHE_DIR,
								'compile_dir'     => SMARTY_COMPILE_DIR,
								'plugins_dir'     => SMARTY_PLUGINS_DIR,
								'config_dir'      => $controller->getModuleDir() . 'config/',
								'app_name'        => $controller->getCurrentModule(),
								'debug_tpl'       => SMARTY_DEBUG_TPL,
								'debugging_ctrl'  => DEBUGGING_CTRL,
								'debugging'       => SMARTY_DEBUGGING,
								'caching'         => SMARTY_CACHING,
								'force_compile'   => SMARTY_FORCE_COMPILE
							);
         
			require_once(RENDERER_DIR . 'SmartyRenderer.class.php');

			$renderer =& new SmartyRenderer($controller);

			$smarty = & $renderer->getEngine();
			
			foreach ($params AS $key => $value)
			{
				$smarty->$key = $value;
			}

			// set the renderer as a request attribute so we can retrieve it
			$request->setAttributeByRef('SmartyRenderer', $renderer);

			// execute chain
			$filterChain->execute($controller, $request, $user);

			// remove renderer
			$request->removeAttribute('SmartyRenderer');
		} 
		
		else
		{
			$filterChain->execute($controller, $request, $user);
		}
	}
}

?>