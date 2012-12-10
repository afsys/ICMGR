<?php
	
/**
 * Tickets list 
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

class KB
{
	/**
	 * Database object
	 * @var sxDB
	 */
	var $db = null;
	
	var $url_prefix = null;
	
	function KB($url_prefix = "")
	{
		$this->db =& sxDB::instance();
	}
	
	/**
	 * Return categories path accordinly to current category.
	 *
	 * @param  integer   $cat_id         Current category Id-number
	 * @param  boolean   $curr_as_link   If true current item showing as link, else as simple text
	 * @return string                    Categories path in html string
	 */
	function GetCategoriesPath($cat_id, $curr_as_link = false)
	{
		$backtrace_path = "";
		$_cat_id = $cat_id;
		while ($_cat_id != 0)
		{
			// get categories list
			$this->db->q("
				SELECT 
				   cat_id        AS id,
				   cat_parent_id AS parent_id,
				   cat_caption   AS caption
				FROM 
				   #_PREF_kb_categories
				WHERE 
				   cat_id  = $_cat_id
			");
		
			$delim = " <font size='-2'>&gt;</font> ";

			// index.php?module=KnowledgeBase&action=Categories&
			$cat = $this->db->fetchAssoc();
			if ($_cat_id == $cat_id && !$curr_as_link) $cat_capt = $delim.'<span class="path">'. $cat['caption'] .'</span>';
			else $cat_capt = $delim.'<a class="path" href="?cat_id='. $cat['id'] .'">'. $cat['caption'] .'</a>';
			$backtrace_path = $cat_capt . $backtrace_path;
			$_cat_id = $cat['parent_id'];                
		}
		
		if ($cat_id == 0 && !$curr_as_link) $cats_path = '<span class="path">Root</span>';
		else $cats_path = '<a class="path" href="?">Root</a>'. $backtrace_path;
	
		return "Location: $cats_path";
	}
	
	/**
	 * Returns array with item info
	 */
	function GetItemInfo($item_id, $update_counter = false)
	{
		// get items list
		$this->db->q("
			SELECT 
			   cat_id        AS cat_id,
			   item_caption  AS caption,
			   item_notes    AS notes,
			   item_viewed   AS viewed_count,
			   DATE_FORMAT(created_at, '%M %d, %Y') AS created_at
			FROM 
			   #_PREF_kb_items
			WHERE 
			   item_id = $item_id          
		");
		$item = $this->db->fetchAssoc();

		if ($update_counter)
		{
			$this->db->q("UPDATE #_PREF_kb_items SET item_viewed = item_viewed + 1 WHERE item_id = $item_id");
		}
		
		return $item;
	}
	
	/**
	 * Returns array with item notes array
	 */
	function GetItemNotes($item_id)
	{
		// get items list
		$this->db->q("
			SELECT
			   note_id   AS id,
			   note_user AS user,
			   note_text AS text,
			   DATE_FORMAT(note_date, '%M %d, %Y') AS date
			FROM
			   #_PREF_kb_items_notes
			WHERE
			   item_id = $item_id
			ORDER BY
			   note_date DESC      
		");
		$notes = array();
		while ($note = $this->db->fetchAssoc()) $notes[] = $note;
		return $notes;
	}
	
	/**
	 * Returns array of items
	 *
	 * @param  array   $where_clause      where clause
	 * @return array                      array of returned items
	 */	 
	function GetItemsQuery($where_clause)
	{
		// get items list
		$this->db->q("
			SELECT
			   i.cat_id          AS cat_id,
			   i.item_id         AS id,
			   i.item_caption    AS caption,
			   i.item_notes      AS notes,
			   i.item_viewed     AS viewed_count,
			   DATE_FORMAT(i.created_at, '%M %d, %Y') AS created_at,
			   expiration_date,
			   TO_DAYS(expiration_date) - TO_DAYS(NOW()) AS expired_in_days,
			   COUNT(ino.item_id) AS notes_count,
			   AVG(ir.rait_value) AS notes_raiting
			FROM
			   #_PREF_kb_items i
			   LEFT JOIN #_PREF_kb_items_notes ino ON i.item_id = ino.item_id
			   LEFT JOIN #_PREF_kb_items_raiting ir ON i.item_id = ir.item_id
			",
			
			$where_clause
		);
		
		$items = array();
		while ($item = $this->db->fetchAssoc()) $items[] = $item;   
		return $items;
	}

	/**
	 * Returns array of items by parent item or string search criteria
	 *
	 * @param  integer   $where              parent directory id or where clause
	 * @param  string    $search_string      filter items
	 * @return array                         array of returned items
	 */	 
	function GetItems($where, $search_string = null)
	{
		$where_clause = array();
		if (is_numeric($where)) $where_clause['i.cat_id'] = $where; 
		else if (is_array($where)) $where_clause = $where;
		if ($search_string) {
			$where_clause['AND'] = array (
				 '#DELIM' => 'OR',
				 'i.item_caption' => array('LIKE', "%$search_string%"),
				 'i.item_notes'   => array('LIKE', "%$search_string%")
			);
		}
		
		$where_clause = array(
			'#WHERE' => $where_clause,
			'#GROUP' => 'cat_id, id, caption, notes, viewed_count, created_at',
			'#ORDER' => 'i.item_caption'
		);
		
		return $this->GetItemsQuery($where_clause);
	}
	
	/**
	 * Returns array of top items
	 *
	 * @param  string   $top_type     enum ('most_vieved')
	 * @return array                         array of returned items
	 */
	function GetTopItems($top_type) {
		$ids = array();
		
		switch ($top_type) {
			case 'most_rated':
				$where_clause = array(
					'#GROUP'  => 'cat_id, id, caption, notes, viewed_count, created_at',
					'#ORDER'  => 'notes_raiting DESC',
					'#LIMIT'  => 2,
					'#HAVING' => array('AVG(ir.rait_value)' => 'IS NOT NULL'),
				);
				
				break;
				
			case 'most_viewed':
				$where_clause = array(
					'#GROUP'  => 'cat_id, id, caption, notes, viewed_count, created_at',
					'#ORDER'  => 'viewed_count DESC',
					'#LIMIT'  => 2,
					'#HAVING' => array('AVG(i.item_viewed)' => 'IS NOT NULL'),
				);
				
				break;
		}
		
		return $this->GetItemsQuery($where_clause);
	}

	
	/**
	 * Returns array of categories
	 */
	function getCategories($parent_cat_id)
	{
		if (defined('DO_NOT_USE_TEMPORARY_TABLES')) $use_temporary = '';
		else $use_temporary = 'TEMPORARY';
		
		if (empty($use_temporary)) $tmp_table = $this->db->getOne("SHOW TABLES LIKE '#_PREF_kb_categories_items_count'");
		
		if (empty($tmp_table) || !empty($use_temporary)) {
			// create temporary table for items counting
			$this->db->q("
			  CREATE $use_temporary TABLE #_PREF_kb_categories_items_count
				SELECT
				   p.cat_id          AS cat_id,
				   COUNT(it.cat_id)  AS items_cnt
				FROM
				   #_PREF_kb_categories p
				   LEFT JOIN #_PREF_kb_items it ON p.cat_id = it.cat_id
				WHERE
				   p.cat_parent_id = $parent_cat_id
				GROUP BY
				   p.cat_id,
				   p.cat_caption,
				   p.cat_notes
			");
		}

		// get categories list
		$this->db->q("
			SELECT
			   p.cat_id          AS id,
			   p.cat_caption     AS caption,
			   p.cat_notes       AS notes,
			   ic.items_cnt      AS items_cnt,
			   COUNT(i.cat_id)   AS cats_cnt
			FROM
			   #_PREF_kb_categories p
			   LEFT JOIN #_PREF_kb_categories i ON p.cat_id = i.cat_parent_id
			   LEFT JOIN #_PREF_kb_categories_items_count ic ON p.cat_id = ic.cat_id
			WHERE
			   p.cat_parent_id = $parent_cat_id
			GROUP BY
			   p.cat_id,
			   p.cat_caption,
			   p.cat_notes
			ORDER BY
			   p.cat_caption
		");
		
		//$categories = array(1 => array(), 2 => array(), 3 => array());
		$categories = array();
		while ($category = $this->db->fetchAssoc()) $categories[] = $category;
		
		$this->db->q("DROP TABLE IF EXISTS #_PREF_kb_categories_items_count");
		
		return $categories;
	}
	


}