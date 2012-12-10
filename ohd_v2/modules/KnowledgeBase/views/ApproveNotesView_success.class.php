<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to add user.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */
    
error_reporting(E_ALL);

class ApproveNotesView extends View
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
		
		$db->q("
			SELECT 
			   c.cat_caption,
			   i.item_caption,
			   n.item_id,
			   n.note_id,
			   n.note_user,
			   n.note_text,
			   DATE_FORMAT(n.note_date, '%Y-%m-%d') AS note_date
			FROM #_PREF_kb_items_notes n
			   INNER JOIN #_PREF_kb_items i ON n.item_id = i.item_id
			   INNER JOIN #_PREF_kb_categories c ON c.cat_id = i.cat_id
			WHERE 
			   n.note_approved = 0
		");
		
		$notes = array();
		while ($data = $db->fetchAssoc()) $notes[] = $data;
		$renderer->setAttribute('notes', $notes);

		

				
		$renderer->setAttribute('pageBody', 'approveNotes.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}

	/**
	* There's no cleanup to do for this view.
	*
	function cleanup ()
	{

	} */
}
?>