<?php
	
/**
 * ShowCategoriesAction class
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @version    1.21 Beta
 * @created    26 September 2005
 */

error_reporting(E_ALL);

class CategoriesAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
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
		return null;
	}
	
	function isSecure()
	{
		return false;   
	}
}

?>