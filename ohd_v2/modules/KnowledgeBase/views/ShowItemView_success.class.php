<?PHP

/**
 * ShowItemView class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.00 Alpha
 * @created    26 September 2005
 */

error_reporting(E_ALL); 
require 'KnowledgeBaseViewItem.class.php';  

class ShowItemView extends KnowledgeBaseViewItem
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function &execute(&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = &$request->getAttribute('SmartyRenderer');
		$renderer->setAttribute('form', $request->getAttribute('form'));
		$this->db =& sxDb::instance();
		
		$is_admin = $_SESSION['authenticated'];        
		$renderer->setAttribute('is_admin', $is_admin);          

		$item_id = $request->getParameter('item_id');
		$renderer->setAttribute('item_id', $item_id);
		
		__('');
		$renderer->setAttribute('page_caption', __('Show Item'));
		
		// views += 1
		$query = "
			UPDATE 
			   #_PREF_kb_items
			SET
			   item_viewed = item_viewed + 1
			WHERE 
			   item_id = $item_id
				
			";
		$this->db->query($query);        

		// get item info
		$query = "
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
				
			";
			
		//echo $query;   
		$res = $this->db->query($query);
		$item = $this->db->fetchAssoc();
		
		
		$user_ip = $_SERVER['REMOTE_ADDR'];
		$no_vote = !$this->db->getOne("SELECT COUNT(*) FROM #_PREF_kb_items_raiting WHERE rait_user_ip = '$user_ip' AND item_id = '$item_id'" );
		$renderer->setAttribute('user_ip',      $user_ip);
		$renderer->setAttribute('no_vote',      $no_vote);
			   
		$renderer->setAttribute('cat_id',       $item['cat_id']);
		$renderer->setAttribute('item_caption', $item['caption']);
		$renderer->setAttribute('item_notes',   $item['notes']);
		
		// get user notes for item
		$user_notes = array();
		$query = "
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
			";  
		$res = $this->db->query($query);
		while ($user_note = $this->db->fetchAssoc()) $user_notes[] = $user_note;
		$renderer->setAttribute('user_notes', $user_notes);        
			
		// categories list and path generation
		$renderer->setAttribute('cats_path',   $this->GetCategoriesPath($item['cat_id'], true));
		$renderer->setAttribute('cat_options', $this->GetCategoriesOptionsList());
		
		$renderer->setAttribute('pageBody', 'showItem.html');
		
		// select form for administrator and simple user
		if ($user->isAuthenticated())
		{
			$renderer->setTemplate('../../index.html');
		}
		else
		{
			$renderer->setTemplate('../../user_index.html');
		}            
		//if ($is_admin) $renderer->setTemplate('../../index.html');
		//else $renderer->setTemplate('index_user.html');
		
		return $renderer;
	}


}
?>