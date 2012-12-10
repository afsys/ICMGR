<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to view filters list.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Mar 22, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);

class FilterAction extends Action
{
	var $message;
	
	function execute (&$controller, &$request, &$user)
	{
		$will =  $request->getParameter('will');
		$db   =& sxDb::instance();

		if ('del' == $will)
		{
			$this->handle_delete($db, $request->getParameter('id'));
			$controller->forward('EmailPiping', 'ListFilters');
			return VIEW_NONE;
		}

		if ($request->getParameter('go'))
		{
			$FilterData = $request->getParameter('filter');
			$this->message = __('Please fill in some criteria');
			
			/*
			echo "<pre>";
			var_dump($will);
			var_dump($FilterData);
			die('ggg'); /**/
			
			
			if ('add' == $will)
			{
				if ($this->handle_post_add($db, $FilterData))
				{
					header('Location: index.php?module=EmailPiping&action=ListFilters');
					exit();
				}
			}

			if ('edit' == $will)
			{
				if ($this->handle_post_edit($db, $FilterData))
				{
					header('Location: index.php?module=EmailPiping&action=ListFilters');
					exit();
				}
			}
			$request->setAttribute('message', $this->message);
		}
		return VIEW_SUCCESS;
	}	


	function has_criterion($FilterData)
	{
		unset($FilterData['id_group']);
		foreach($FilterData as $field)
		{
			$field = trim((string)$field);
			if (!empty($field))
			{
				return true;
			}
		}
		return false;
	}


	function handle_post_add(&$db, $FilterData)
	{
		if (!$this->has_criterion($FilterData))
		{
			return false;
		}
		
		$res = $db->qI('#_PREF_email_filter', $FilterData);
		$this->message = __('Filter information added');
		
		return true;
	}

	function handle_post_edit(&$db, $FilterData)
	{
		if (!$this->has_criterion($FilterData))
		{
			return false;
		}
		
		$res = $db->qI('#_PREF_email_filter', $FilterData, 'UPDATE', array('id' => $FilterData['id']));

		$this->message = __('Filter information updated');
		return true;
	}

	function handle_delete(&$db, $id)
	{
		$query = 'DELETE FROM #_PREF_email_filter WHERE id='.(int)$id;
		$db->query($query);
	}



	function getDefaultView (&$controller, &$request, &$user)
	{
		return VIEW_SUCCESS;
	}

	function handleError (&$controller, &$request, &$user)
	{
		$controller->forward(ERROR_404_MODULE, ERROR_404_ACTION);
		return VIEW_NONE;
	}

	function registerValidators (&$validatorManager, &$controller, &$request, &$user)
	{

	}

	function getPrivilege()
	{
		return null;
	}

	function isSecure()
	{
	
	}

}
?>