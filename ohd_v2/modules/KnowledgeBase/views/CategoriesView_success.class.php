<?php

/**
 * ShowCategoriesView class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    26 September 2005
 */
 
require_once 'Classes/kb.class.php';
require_once 'KnowledgeBaseViewItem.class.php'; 

class CategoriesView extends KnowledgeBaseViewItem
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = &$request->getAttribute('SmartyRenderer');
		$renderer->setAttribute('form', $request->getAttribute('form'));
		$this->db =& sxDb::instance();
		$kb = new KB();
		
		$is_customer = $user->getAttribute('is_customer') || !$user->isAuthenticated();
		$user_right  = $user->getAttribute('user_rights');
		$is_admin    = ($user_right && SR_KB_MANAGE_CATS) == SR_KB_MANAGE_CATS;
		$renderer->setAttribute('is_admin', $is_admin);        
		
		$cat_id = $request->getParameter('cat_id');
		if (!is_numeric($cat_id)) $cat_id = 0;
		
		// categories path generation
		$renderer->setAttribute('cats_path', $this->GetCategoriesPath($cat_id));

		// get catefories and items
		$categories  = $kb->GetCategories($cat_id);
		$items       = $kb->GetItems($cat_id);
		if ($is_customer && $cat_id == 0) {
			// $renderer->setAttribute('most_rated',  $kb->GetTopItems('most_rated'));
			$renderer->setAttribute('most_viewed', $kb->GetTopItems('most_viewed'));
		}
		
		// break categories list by rows
		$rows_count = 3;
		$renderer->setAttribute('rows_count', $rows_count);
		$renderer->setAttribute('rows_width', 100 / $rows_count);
		
		if ($rows_count > 0) {
			$cats = array();
			$i = 0;
			foreach ($categories as $cat) {
				if (!isset($cats[$i])) $cats[$i] = array();
				$cats[$i][] = $cat;
				if ($i >= $rows_count-1) $i = 0;  else $i++;
			}
			$categories = $cats;
		}

		
		// fetch items
		$renderer->setAttribute('is_customer', $is_customer);
		$renderer->setAttribute('is_authenticated', $user->isAuthenticated() ? 1 : 0);
		
		$renderer->setAttribute('categories',  $categories);
		$renderer->setAttribute('items',       $items);
		$renderer->setAttribute('directory_caption', 'OSS-System Knowelege Database');
		
		$renderer->setAttribute('cat_id', $cat_id);
		$renderer->setAttribute('pageBody', 'categories.html');
		

		// select form for administrator and simple user
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