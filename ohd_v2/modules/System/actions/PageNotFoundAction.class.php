<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Action for ...
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 4, 2005
 * @version    1.00 Beta
 */


class PageNotFoundAction extends Action
{
	 function execute ()
	 {
		 return VIEW_SUCCESS;
	 }

	/**
	 * Retrieve the default view to be displayed when getRequestMethods() does
	 * not return the current method.
	 *
	 * @return one of the defined views or a custom view.
	 */
	function getDefaultView ()
	{

		// our default view is the success view, since no validation or
		// execution will occur.
		return VIEW_SUCCESS;

	}

	/**
	 * This action is not secure, so we do not need to specify a privilege.
	 *
	 function getPrivilege ()
	 {

	 }
	 */

	/**
	 * Retrieve all request methods this action will handle.
	 *
	 * NOTE: When a request is made for this action with a different request method
	 *       than provided here, the view is determined by getDefaultView().
	 *
	 * @return one of the defined request methods, or multiple.
	 */
	function getRequestMethods ()
	{

		// we want to skip validation and execution and go directly to the
		// view, so we tell the framework that no request methods are served
		// by this action.
		return REQ_NONE;
	}

	/**
	 * No errors occur, so there's nothing to validate.
	 *
	 function handleError ()
	 {

	 }
	 */

	 function isSecure ()
	 {
		return FALSE;
	 }
	 

	/**
	 * Nothing to validate.
	 *
	 function registerValidators (&$validatorManager)
	 {

	 }
	 */

	/**
	 * Nothing to validate.
	 *
	 function validate ()
	 {

	 }
	 */

}

?>
