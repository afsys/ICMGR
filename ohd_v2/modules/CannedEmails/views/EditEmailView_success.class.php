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

class EditEmailView extends View
{
	
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
		$id = $request->getParameter('id');
		if ($id)
		{
			$renderer->setAttribute('id', $id);
			$res = $db->query("
				SELECT 
				  email_id,
				  cat_id,
				  email_caption,
				  email_content,
				  highlighted
				FROM
				  #_PREF_canned_emails
				WHERE
				  email_id = $id
				");
		
			$email = $db->fetchAssoc();
			$renderer->setAttribute('email', $email);
		}
		
		// get cats
		$db->q("SELECT * FROM #_PREF_canned_emails_categories");
		$cats = array();
		while ($data = $db->fetchAssoc()) $cats[] = $data;
		$renderer->setAttribute('cats', $cats);        

		$renderer->setAttribute('pageBody', 'editEmail.html');
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