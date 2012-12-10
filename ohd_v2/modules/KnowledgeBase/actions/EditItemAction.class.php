<?PHP
	
/**
 * EditItemAction class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    26 September 2005
 */


class EditItemAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		$db =& sxDb::instance();
		if (!is_null($request->getParameter('submit_item')))
		{        
			$this->processPostData($db, $controller, $request, $user);
		}         
		
		return VIEW_SUCCESS;
	}
	
	function processPostData(&$db, &$controller, &$request, &$user)
	{
		$cat_id          = $request->getParameter('cat_id');
		$item_caption    = $request->getParameter('item_caption');
		$item_notes      = $request->getParameter('item_notes');
		$expiration_date = $request->getParameter('expiration_date');
		$root_page       = $request->getParameter('root_page');
		if ($expiration_date) $expiration_date = "'$expiration_date'";
		else $expiration_date = 'NULL';
		

		$item_id = $request->getParameter('item_id');
		
		// insert record
		if ($item_id == null) {
			$item_id = $db->getOne('SELECT IFNULL(MAX(item_id)+1,1) FROM #_PREF_kb_items');
			$query = "
				INSERT INTO #_PREF_kb_items (cat_id, item_id, item_caption, item_notes, created_at, expiration_date) 
					VALUES ($cat_id, $item_id, '".  mysql_real_escape_string($item_caption) ."', '".  mysql_real_escape_string($item_notes) ."', NOW(), $expiration_date) ";
		}
		// update record
		else {
			$query = "
				UPDATE #_PREF_kb_items 
				SET
				   item_caption    = '".  mysql_real_escape_string($item_caption) ."', 
				   item_notes      = '".  mysql_real_escape_string($item_notes)   ."',
				   cat_id          = $cat_id,
				   expiration_date = $expiration_date
				WHERE
				   item_id = $item_id
			";
		}
   
		$res = $db->query($query);        
		
		switch ($root_page) {
			case 'ExpiredItems':
				header('Location: index.php?module=KnowledgeBase&action=ExpiredItems');
				break;
				
			default:
				header('Location: index.php?module=KnowledgeBase&action=Categories&cat_id='.$cat_id);
				break;
		}
		
		die();
		//$controller->forward('KnowledgeBase', 'Categories');
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;
	}

	function handleError (&$controller, &$request, &$user)
	{
		// don't handle errors, just redirect to error 404 action
		$controller->forward(ERROR_404_MODULE, ERROR_404_ACTION);

		return VIEW_NONE;
	}

	function registerValidators (&$validatorManager, &$controller, &$request, &$user)
	{

	}
	
	function getPrivilege()
	{
		return null;
	}

	function isSecure()
	{
		return true;   
	}
}

?>