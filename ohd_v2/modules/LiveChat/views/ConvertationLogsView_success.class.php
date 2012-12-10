<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Show list of users query.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    May 02, 2006
 * @version    1.00 Beta
 */
 
require_once 'lib/Classes/livechat.class.php'; 
require_once 'lib/Classes/groups.class.php'; 
require_once 'lib/Classes/Pager2.class.php'; 

class ConvertationLogsView extends View
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
		$db       =& sxDb::instance();

		// get groups list
		$groups  =  new Groups();
		$renderer->setAttribute('groups', $groups->GetDataList());

		// get curr group logs
		$curr_group_id = $request->getParameter('curr_group_id');
		if ($curr_group_id) {
			$renderer->setAttribute('curr_group_id', $curr_group_id);
			
				// paging
				/* $cnt = $db->getOne('
					SELECT COUNT(*) 
					FROM #_PREF_lc_users users
					   JOIN #_PREF_groups groups ON users.req_group_id = groups.group_id
					WHERE groups.group_id = '. $curr_group_id .'
					'); */
					
				 $cnt = $db->getOne('SELECT COUNT(*) FROM #_PREF_lc_sessions', array('group_id' => $curr_group_id));
				
				
				//$_GET['curr_group_id'] = $curr_group_id;
				
				$pager = new Pager2($cnt, 15, new pagerHtmlRenderer());
				$pager -> setDelta(5);
				$pager -> setFirstPagesCnt(3);
				$pager -> setLastPagesCnt(3);
				$pager -> setPageVarName("page");
				$pages = $pager->render();
				$renderer->setAttribute('pages', $pages);
			
			/*echo "<pre>";
			var_dump($pages);
			echo "</pre>"; /**/
			
			$curr_page = $request->getParameter('page');
			if (!$curr_page) $curr_page = 0;
			
			// transcripts list
			$transcripts = LiveChat::GetSessionsHeaders(
				array (
					'#WHERE' => array('group_id' => $curr_group_id),
					'#LIMIT' => array(15*$curr_page, 15),
					'#ORDER' => 'created_at DESC'
				)
			);
			
			//dump($transcripts);
			
			$renderer->setAttribute('transcripts', $transcripts);

		}
		
		$renderer->setAttribute('pageBody', 'convertation_logs.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}

}
?>