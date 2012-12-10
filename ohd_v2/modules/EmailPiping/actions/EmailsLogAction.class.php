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


class EmailsLogAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{  
	}

    function getDefaultView ()
    {

        // our default view is the success view, since no validation or
        // execution will occur.
        return VIEW_SUCCESS;

    }

    function getRequestMethods ()
    {

        // we want to skip validation and execution and go directly to the
        // view, so we tell the framework that no request methods are served
        // by this action.
        return REQ_NONE;
    }

     function isSecure ()
     {
        return TRUE;
     }
}

?>