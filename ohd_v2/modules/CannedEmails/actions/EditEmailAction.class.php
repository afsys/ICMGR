<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to manage system preferences.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 5, 2005
 * @version    1.00 Beta
 */
	
error_reporting(E_ALL);

class EditEmailAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{       
		$db =& sxDb::instance();
		$save_email = $request->getParameter('save_email');
		
		if ($save_email) {              
			$id = $request->getParameter('id');
			$email_caption = $request->getParameter('email_caption');
			$email_content = $request->getParameter('email_content');
			$cat_id        = $request->getParameter('cat_id');
			$highlighted   = $request->getParameter('highlighted');
			
			// update record
			if ($id) {
				$db->query("
					UPDATE #_PREF_canned_emails 
					  SET
						cat_id        = '$cat_id',
						email_caption = '". mysql_real_escape_string($email_caption) ."',
						email_content = '". mysql_real_escape_string($email_content) ."',
						highlighted   =  ". mysql_real_escape_string($highlighted)   ."
					  WHERE email_id = $id");
			}
			// create new email
			else {
				$db->query("
					INSERT INTO #_PREF_canned_emails (email_caption, email_content, cat_id, highlighted)
					  VALUES ('". mysql_real_escape_string($email_caption) ."', '". mysql_real_escape_string($email_content). "', '$cat_id', ". mysql_real_escape_string($highlighted) .")");
			}
			
			
			
			$controller->forward('CannedEmails', 'ShowList');
			return VIEW_NONE; 
			//header('location: index.php?module=CannedEmails&action=ShowList');
			//die();
		}
		

		return VIEW_SUCCESS;
	}
	
	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;
	}

	function handleError (&$controller, &$request, &$user)
	{
		// don't handle errors, just redirect to error 404 action
		$controller->forward(ERROR_404_MODULE, ERROR_404_ACTION);

		return VIEW_NONE;
	}

	function registerValidators (&$validatorManager, &$controller, &$request, &$user)
	{

	}
	
	function getPrivilege()
	{
		return NULL;
	}
	
	function isSecure()
	{
		return TRUE;    
	}
}

?>
	