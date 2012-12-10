<?php

/**
 * KnowledgeBaseViewItem class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    26 September 2005
 */

class KnowledgeBaseViewItem extends View
{
	var $db;
	
	/**
	 * Return categories path accordinly to current category.
	 *
	 * @param  integer   $cat_id         Current category Id-number
	 * @param  boolean   $curr_as_link   If true current item showing as link, else as simple text
	 * @return string                    Categories path in html string
	 */
	function GetCategoriesPath ($cat_id, $curr_as_link = false)
	{
        // alias inherited data for easy access
        $this->db   =& sxDB::instance();

		
		$backtrace_path = "";
		$_cat_id = $cat_id;
		while ($_cat_id != 0)
		{
			// get categories list
			$query = "
				SELECT 
				   cat_id        AS id,
				   cat_parent_id AS parent_id,
				   cat_caption   AS caption
				FROM 
				   #_PREF_kb_categories
				WHERE 
				   cat_id  = $_cat_id
				";
			//echo "$query<br>";
			
			$delim = " <font size='-2'>&gt;</font> ";
			$res = $this->db->query($query);

			$cat = $this->db->fetchAssoc();
			if ($_cat_id == $cat_id && !$curr_as_link) $cat_capt = $delim.'<span class="path">'. $cat['caption'] .'</span>';
			else $cat_capt = $delim.'<a class="path" href="index.php?module=KnowledgeBase&action=Categories&cat_id='. $cat['id'] .'">'. $cat['caption'] .'</a>';
			$backtrace_path = $cat_capt . $backtrace_path;
			$_cat_id = $cat['parent_id'];                

		}

		__('');
		
		if ($cat_id == 0 && !$curr_as_link) $cats_path = '<span class="path">'. __('Root') .'</span>';
		else $cats_path = '<a class="path" href="index.php?module=KnowledgeBase&action=Categories">'. __('Root') .'</a>'. $backtrace_path;
	
		return __('Location:')." $cats_path";
	}

	/**
	 * Return categories path accordinly to current category.
	 *
	 * @param  integer   $cat_id         Top category Id-number
	 * @param  string    $prefix         String prefix to show items ierarchy.
	 * @return array     Array of categories caption
	 */
	function GetCategoriesOptionsList($cat_id = 0, $prefix = "&nbsp;&nbsp;&nbsp;")
	{
		$db =& sxDB::instance();
		__('');
		if ($cat_id == 0) $cat_items = array(0 => __('Root item'));
		else $cat_items = array();
		
		
		// make cat_options array
		$r = $db->q("
			SELECT 
			   cat_id       AS id,
			   cat_caption  AS caption,
			   cat_notes    AS notes
			FROM 
			   #_PREF_kb_categories
			WHERE 
			   cat_parent_id = $cat_id
			ORDER BY caption
		");

		while ($category = $db->fetchAssoc($r))
		{
			$cat_items[$category['id']]= $prefix.$category['caption'];
			$sub_items = KnowledgeBaseViewItem::GetCategoriesOptionsList($category['id'], $prefix.$prefix);
			foreach ($sub_items as $k => $v)
			{
				$cat_items[$k]= $v;
			}
			
		}

		return $cat_items;
	}
	
}


/**
 * Return all items accordinly to current category.
 *
 * @param  integer   $cat_id         Top category Id-number
 * @param  string    $prefix         String prefix to show items ierarchy.
 * @return array     Array of categories caption
 */
function KB_GetAllItemsList($cat_id = 0, $prefix = "&nbsp;&nbsp;&nbsp;")
{
	$db =& sxDB::instance();

	if ($cat_id == 0) $cat_items = array('dir_0' => array('type' => 'dir', 'caption' => '[Root item]'));
	else $cat_items = array();
	
	
	// get items list
	$r = $db->q("
		SELECT 
		   item_id       AS id,
		   cat_id        AS cat_id, 
		   item_caption  AS caption
		FROM 
		   #_PREF_kb_items
		WHERE 
		   cat_id  = $cat_id
		ORDER BY caption
	"); 
	
	while ($category = $db->fetchAssoc($r))
	{
		$cat_items[$category['id']]= array('type' => 'item', 'caption' => $prefix.$category['caption']);
	}	
	
	// make cat_options array
	$r = $db->q("
		SELECT 
		   cat_id       AS id,
		   cat_caption  AS caption,
		   cat_notes    AS notes
		FROM 
		   #_PREF_kb_categories
		WHERE 
		   cat_parent_id = $cat_id
		ORDER BY caption
	");

	while ($category = $db->fetchAssoc($r))
	{
		$cat_items['dir_'.$category['id']]= array('type' => 'dir', 'caption' => $prefix."[".$category['caption']."]");
		$sub_items = KB_GetAllItemsList($category['id'], $prefix.$prefix);
		foreach ($sub_items as $k => $v) $cat_items[$k]= $v;
	}

	return $cat_items;
}

?>