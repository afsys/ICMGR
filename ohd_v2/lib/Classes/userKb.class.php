<?php

require_once 'kb.class.php';
	
/**
 * Tickets list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

class UserKB
{
	/**
	 * Database object
	 * @var sxDB
	 */
	var $db = null;

	/**
	 * Database object
	 * @var sxDB
	 */
	var $kb = null;
	
	/**
	 * Relative path to OHD.
	 * @var url_prefix
	 */
	var $url_prefix = null;

	
	function UserKB($url_prefix)
	{
		$this->url_prefix = $url_prefix;
		
		require_once (OHD_LIB_DIR . 'Classes/sx_db.class.php');
		require_once (OHD_LIB_DIR . 'Classes/sx_db_ini.class.php');
		require_once (OHD_LIB_DIR . 'Classes/userKb.class.php');
		$this->db =& sxDB::instance();
		$this->kb = new KB();
		
		include("SmartyRendererStandAlone.class.php");
		$_smarty = new SmartyRendererStandAlone(BASE_DIR."/templates", "ticket_form.html", true);
		$this->smarty =& $_smarty->_smarty;
	}
	
	function get_vars()
	{
		if (get_magic_quotes_gpc())
		{
			return $this->array_stripslashes($_REQUEST);
		}
		return $_REQUEST;
	}	
	
	function array_stripslashes($ar)
	{
		foreach($ar as $k=>$v) {
			if(is_array($v)) {
				$ar[$k] = $this->array_stripslashes($v);
			}
			else $ar[$k] = stripslashes($v);
		}
		
		return $ar;
	}
	
	
	function process_request(&$kb_form) 
	{
		$vars = $this->get_vars();
		$this->smarty->assign("vars", $vars);
		
		// show item page
		if (!empty($vars['item_id']))
		{
			$item_id = $vars['item_id'];
			$item_info   = $this->kb->GetItemInfo($item_id, true);
			$cats_path   = $this->kb->GetCategoriesPath($item_info['cat_id'], true);
			$user_notes  = $this->kb->GetItemNotes($item_id);


			$user_ip = $_SERVER['REMOTE_ADDR'];
			$no_vote = !$this->db->getOne("SELECT COUNT(*) FROM #_PREF_kb_items_raiting WHERE rait_user_ip = '$user_ip' AND item_id = '$item_id'" );
			$this->smarty->assign('user_ip',      $user_ip);
			$this->smarty->assign('no_vote',      $no_vote);
			
			$this->smarty->assign('item_info',    $item_info);
			$this->smarty->assign('cats_path',    $cats_path);
			$this->smarty->assign('user_notes',   $user_notes);
			$this->smarty->assign('page_caption', 'Show Item');
			
			$this->smarty->assign('url_prefix',   $this->url_prefix);
			$this->smarty->assign('item_id',      $item_id);
			
			
			$kb_form = $this->smarty->fetch('kb_item.html');
		}
		// show category page
		else
		{
			$cat_id = (!empty($vars['cat_id']) && is_numeric($vars['cat_id'])) ? $vars['cat_id'] : 0;
			$categories = $this->kb->GetCategories($cat_id);
			$items      = $this->kb->GetItems($cat_id);
			$cats_path  = $this->kb->GetCategoriesPath($cat_id);
			
			if ($cat_id == 0) {
				$this->smarty->assign('most_rated',  $this->kb->GetTopItems('most_rated'));
				$this->smarty->assign('most_viewed', $this->kb->GetTopItems('most_viewed'));
			}
			
			// make rows for categories
			$rows_count = 3;
			$this->smarty->assign('rows_count', $rows_count);
			$this->smarty->assign('rows_width', 100 / $rows_count);
			
			if ($rows_count > 0)
			{
				$cats = array();
				$i = 0;
				foreach ($categories as $cat)
				{
					if (!isset($cats[$i])) $cats[$i] = array();
					$cats[$i][] = $cat;
					if ($i >= $rows_count-1) $i = 0;  else $i++;
				}
				$categories = $cats;
			}
			
			$this->smarty->assign('cat_id',      $cat_id);
			$this->smarty->assign('cats_path',   $cats_path);
			$this->smarty->assign('categories',  $categories);
			$this->smarty->assign('items',       $items);
			
			$kb_form = $this->smarty->fetch('kb_category.html');
		}
		
		return true;
	}


}

?>