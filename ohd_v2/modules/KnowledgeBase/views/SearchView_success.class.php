<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to manage canned emails templateS.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Feb 6, 2006
 * @version    1.00 Beta
 */
 
error_reporting(E_ALL);


require 'Classes/kb.class.php'; 

class SearchView extends View
{
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = &$request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		$kb       = new KB();
		
		// search 
		$text = $request->getParameter('text');
		$items = $kb->getItems(null, $text);
		$renderer->setAttribute('items', $items);
		
        // categories path generation
        //var_dump($kb->GetCategoriesPath(0, true));
        __('');
        $renderer->setAttribute('cats_path', __('Location:').' <a class="path" href="index.php?module=KnowledgeBase&action=Categories">Root</a>');
        
        // filter message
        $renderer->setAttribute('message_caption', 'Filter criteria: ');
        $renderer->setAttribute('message', $text);

		// fetch page
		$renderer->setAttribute('pageBody', 'search.html');
		if ($user->isAuthenticated()) {
			$renderer->setTemplate('../../index.html');
		}
		else {
			$renderer->setTemplate('../../user_index.html');
		}

		return $renderer;
	}
}
?>