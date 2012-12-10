<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * View for ...
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jul 20, 2006
 * @version    1.00 Beta
 */
 
require_once 'lib/Classes/Pager2.class.php'; 

class EmailLogView extends View
{

	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		$db       =& sxDb::instance();
		$renderer =& $request->getAttribute('SmartyRenderer');

		if ($details_type = $request->getParameter('view_details')) {
			$id = $request->getParameter('id');
			$db->q("SELECT * FROM #_PREF_emails_log", array('id' => $id));
			$info = $db->fetchAssoc();
			$info['backtrace'] = unserialize($info['backtrace']);
			
			$renderer->setAttribute('details_type', $details_type);
			$renderer->setAttribute('info', $info);
			$renderer->setTemplate('mail_log_details.html');
			return $renderer;
		}
		
		// PAGING
		$page_size = 10;
		$cnt = $db->getOne("SELECT COUNT(*) FROM #_PREF_emails_log");
		
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
			SELECT *
			FROM #_PREF_emails_log 
			ORDER BY mail_send_time DESC 
			LIMIT ". ($curr_page * $page_size ) .", $page_size 
		");
		$email_list = array();
		while ($rec = $db->fetchAssoc()) {
			$email_list[] = $rec;
		}
		
		$renderer->setAttribute('pagination', $pagination);
		$renderer->setAttribute('email_list', $email_list);
		
		$renderer->setAttribute('pageBody', 'email_log.html');
		$renderer->setTemplate('../../index.html');

		return $renderer;
	}

}

?>
