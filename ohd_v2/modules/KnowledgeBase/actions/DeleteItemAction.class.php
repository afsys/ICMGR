<?PHP
	
/**
 * DeleteItemAction class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    27 September 2005
 */

class DeleteItemAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		$SysConfig = $user->getAttribute('SysConfig');
		$db =& sxDb::instance();
		
		$item_id = $request->getParameter('item_id');        
		
		if (is_numeric($item_id)) {
			// get paret item id
			$cat_id = $db->getOne("SELECT cat_id FROM #_PREF_kb_items WHERE item_id = $item_id");        
			
			// delete item
			$res = $db->query("DELETE FROM #_PREF_kb_items WHERE item_id = $item_id");
			
			// goto categories page
			header('location: index.php?module=KnowledgeBase&action=Categories&cat_id='.$cat_id);                
			die();
		}
		
		header('location: index.php?module=KnowledgeBase&action=Categories');
		die();        
		
	}
}

?>