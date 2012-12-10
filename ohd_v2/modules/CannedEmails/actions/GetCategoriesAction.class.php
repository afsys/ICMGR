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
	
class GetCategoriesAction extends Action
{
	// http://omni/ohd_new/index.php?module=CannedEmails&action=GetCategories

	function execute (&$controller, &$request, &$user)
	{
		// java script header
		header("Content-type: text/javascript");
		
		// get cats
		$ce = new CannedEmails();
		$cats = $ce->getCategories();

		$str = "";
		foreach ($cats as $cat)
		{
			$str .= "  {id: '". $cat['cat_id']."', caption: '". addcslashes(nl2br($cat['cat_caption']), "\0..\37'!@\177..\377") ."', desc: '". addcslashes(nl2br($cat['cat_desc']), "\0..\37'!@\177..\377") ."'},\n";
		}
		if ($str != "") $str = substr($str, 0, strlen($str)-2); 
		$str = "var categories = [\n". $str ."\n];\n";

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