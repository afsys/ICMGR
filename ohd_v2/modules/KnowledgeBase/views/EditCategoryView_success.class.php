<?php

/**
 * AddCategoryView class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    26 September 2005
 */
 
require 'KnowledgeBaseViewItem.class.php';  

class EditCategoryView extends KnowledgeBaseViewItem
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer   =& $request->getAttribute('SmartyRenderer');
		$oFCKeditor =& $request->getAttribute('FCKeditor');
		$this->db   =& sxDb::instance();
		
		$cat_parent_id = $request->getParameter('cat_parent_id');
		if (!is_numeric($cat_parent_id)) $cat_parent_id = 0;
		
		$cat_id = $request->getParameter('cat_id');
		// edit category
		if ($cat_id != null)
		{
			$query = "
				SELECT 
				   cat_parent_id  AS parent_id,
				   cat_caption    AS caption,
				   cat_notes      AS notes
				FROM 
				   #_PREF_kb_categories
				WHERE 
				   cat_id = $cat_id
				";

			$res = $this->db->query($query);
			
			$category_info = $this->db->fetchAssoc();
			$renderer->setAttribute('cat_caption',   $category_info['caption']);
			$renderer->setAttribute('cat_parent_id', $category_info['parent_id']);
			$cat_notes = $category_info['notes'];
			
			// fake!: __('Edit Category Item');
			// fake!: __('Save info');
			$renderer->setAttribute('page_caption', 'Edit Category Item');
			$renderer->setAttribute('submit_button_caption', 'Save info');
		}
		// add new category
		else
		{
			// fake!: __('Create New Category Item');
			// fake!: __('Add category');
			$renderer->setAttribute('cat_parent_id', $cat_parent_id);
			$renderer->setAttribute('page_caption', 'Create New Category Item');
			$renderer->setAttribute('submit_button_caption', 'Add category');
			$cat_notes = '';
		}
		
		$oFCKeditor->Value = $cat_notes;
		$cat_notes_editor = $oFCKeditor->CreateFCKeditor('cat_notes', '100%', '200px') ;
		$renderer->setAttribute('cat_notes', $cat_notes_editor);        
			  
		// categories list and path generation
		$renderer->setAttribute('cats_path',   $this->GetCategoriesPath($cat_parent_id, true));
		$renderer->setAttribute('cat_options', $this->GetCategoriesOptionsList());

		$renderer->setAttribute('pageBody', 'editCategory.html');
		$renderer->setTemplate('../../index.html');

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