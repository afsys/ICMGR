<?php

/**
 * EditItemView class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    26 September 2005
 */
 
require 'KnowledgeBaseViewItem.class.php';  

class EditItemView extends KnowledgeBaseViewItem
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function &execute(&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer   =& $request->getAttribute('SmartyRenderer');
		$oFCKeditor =& $request->getAttribute('FCKeditor');
		$this->db   =& sxDb::instance();
		
		$item_id = $request->getParameter('item_id');
		
		// add new item
		if ($item_id == null) {
			$cat_id = $request->getParameter('cat_id');
			if (!is_numeric($cat_id)) $cat_id = 0;

			$renderer->setAttribute('page_caption', 'Add New Item');
			$renderer->setAttribute('submit_button_caption', 'Add Item');            
			$item_notes = '';
		}
		// edit item
		else {
			// get item info
			$query = "
				SELECT 
				   cat_id        AS cat_id,
				   item_caption  AS caption,
				   item_notes    AS notes,
				   expiration_date
				FROM 
				   #_PREF_kb_items
				WHERE 
				   item_id = $item_id
					
				";
			$res  = $this->db->query($query);
			$item = $this->db->fetchAssoc();
			
			$renderer->setAttribute('item_caption',    $item['caption']);
			$renderer->setAttribute('expiration_date', $item['expiration_date']);
			$item_notes =  $item['notes'];
			$cat_id = $item['cat_id'];
			
			$renderer->setAttribute('page_caption', 'Edit Item');
			$renderer->setAttribute('submit_button_caption', 'Save info');            
		}

		$renderer->setAttribute('cat_id', $cat_id);
		
		
		$oFCKeditor->Value = $item_notes;
		$item_notes_editor = $oFCKeditor->CreateFCKeditor('item_notes', '100%', '200px') ;
		$renderer->setAttribute('item_notes', $item_notes_editor);
		
		// categories list and path generation
		$renderer->setAttribute('cats_path',   $this->GetCategoriesPath($cat_id, true));
		$renderer->setAttribute('cat_options', $this->GetCategoriesOptionsList());

		
		
		$renderer->setAttribute('root_page', $request->getParameter('root_page'));
		$renderer->setAttribute('pageBody', 'editItem.html');
		$renderer->setTemplate('../../index.html');

		return $renderer;
	}


}
?>