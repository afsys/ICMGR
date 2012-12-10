<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show piping emails log.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    03 Jan, 2006
 * @version    1.00 Beta
 */
 
require_once 'lib/Classes/Pager2.class.php'; 

class EmailsLogView extends View
{

	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		$db =& sxDb::instance();
		$renderer =& $request->getAttribute('SmartyRenderer');

		if ($request->getParameter('view_details')) {
			$id = $request->getParameter('id');
			$db->q("SELECT * FROM #_PREF_piping_emails_log", array('email_id' => $id));
			$info = $db->fetchAssoc();
			$info['email_headers'] = unserialize($info['email_headers']);
			//var_dump($info);
			
			$renderer->setAttribute('info', $info);
			$renderer->setTemplate('email_log_details.html');
			return $renderer;
		}
		
		
		// PAGING
		$page_size = 20;
		$cnt = $db->getOne("SELECT COUNT(*) FROM #_PREF_piping_emails_log");

		$pager = new Pager2($cnt, $page_size , new pagerHtmlRenderer());
		$pager -> setDelta(5);
		$pager -> setFirstPagesCnt(3);
		$pager -> setLastPagesCnt(3);
		$pager -> setPageVarName("page");
		$pages = $pager->render();
		$renderer->setAttribute('pages', $pages);
		
		$curr_page = $request->getParameter('page');
		if (!$curr_page) $curr_page = 0;
		
		$res = $db->query("
			SELECT 
			   *
			FROM #_PREF_piping_emails_log 
			ORDER BY email_added_at DESC 
			LIMIT ". ($curr_page * $page_size ) .", $page_size 
		");
		
		$email_list = array();
		while ($rec = $db->fetchAssoc()) {
			$rec["mail_send_time"] = date("Y.M.d H:i",$rec["umail_send_time"]);
			$email_list[] = $rec;
		}
		
		//$renderer->setAttribute('pagination', $pagination);
		$renderer->setAttribute('email_list', $email_list);
		
		$renderer->setAttribute('pageBody', 'email_log.html');
		$renderer->setTemplate('../../index.html');

		return $renderer;
	}

}

?>
