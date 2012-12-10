<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to save ticket options.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Feb 15, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);    
require_once 'Classes/canned_emails.class.php';    
	
class GetCategoryItemsAction extends Action
{
	// http://omni/ohd_new/index.php?module=CannedEmails&action=GetCategoryItems

	function execute (&$controller, &$request, &$user)
	{
		// java script header
		header("Content-type: text/javascript");

		// get ce
		$ce = new CannedEmails();
		$cat_id = $request->getParameter('cat_id');

		// get default cat
		if (empty($cat_id)) {
			$cats = $ce->getCategories();
			$first_cat = array_shift($cats);
			$cat_id = $first_cat['cat_id'] ;
		}

		// get items
		$items = $ce->getItems(array("#WHERE" => array('cat_id' => $cat_id), "#ORDER" => "email_caption"));
		$str = "";
		foreach ($items as $item)
		{
			$email_content = substr($item['email_content'], 0, 200) . "\n\r...";
			$str .= "  {id: '". $item['email_id']."', caption: '". addcslashes(nl2br($item['email_caption']), "\0..\37'!@\177..\377") ."', desc: '". addcslashes(nl2br($email_content), "\0..\37'!@\177..\377") ."', highlighted: ". ($item['highlighted'] ? 1 : 0) ."},\n";
		}
		if ($str != "") $str = substr($str, 0, strlen($str)-2); 
		$str = "var cat_items = [\n". $str ."\n];\n";

		echo $str;
		return VIEW_NONE;  
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_NONE;
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