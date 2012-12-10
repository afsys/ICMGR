<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Action for authenticating user based on info passed from login form
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 4, 2005
 * @version    1.00 Beta
 */

class AuthenticateUserView extends View
{
    
    /**
     * Execute the view.
     *
     * @return a Renderer instance.
     */
    function & execute (&$controller, &$request, &$user)
    {
    	// alias inherited data for easy access
    	$renderer = & $request->getAttribute('SmartyRenderer');
    	
    	if ($errors = $request->getAttribute('errors'))
    	{
    		$renderer->setAttribute('errors', $errors);	
    	}
    	
    	$renderer->setAttribute('username', $request->getAttribute('username'));
    	$renderer->setAttribute('remoteAddy', $_SERVER['REMOTE_ADDR']);
    	$renderer->setTemplate('login.html');

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