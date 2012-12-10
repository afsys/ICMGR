<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to manage canned emails templateS.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */
		
error_reporting(E_ALL);
require_once 'modules/CannedEmails/config/categories.php';

class ShowListView extends View
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer = &$request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();
		
		// make where clause
		$cur_cat = 'all';
		if ($cur_cat == 'all') $where = '';
		else $where = "WHERE cat_id = '$cur_cat'";
		
	   
		// show data
		$res = $db->query("
			SELECT 
			   ce.email_id,
			   ce.cat_id,
			   ce.email_caption,
			   ce.email_content,
			   cec.cat_caption
			FROM
			   #_PREF_canned_emails ce
			   LEFT JOIN #_PREF_canned_emails_categories cec USING (cat_id)
			$where
			ORDER BY
			   ce.email_caption
			");

		$canned_emails = array();
		while ($c_email = $db->fetchAssoc($res)) $canned_emails[] = $c_email;
		$renderer->setAttribute('canned_emails', $canned_emails);

		$renderer->setAttribute('pageBody', 'showList.html');
		$renderer->setTemplate('../../index.html');

		return $renderer;
	}

	/**
	* There's no cleanup to do for this view.
	*
	function cleanup ()
	{

	}
	 */
}
?>