<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to delete user.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */
    
class UsersDeleteAction extends Action
{
    function execute (&$controller, &$request, &$user)
    {
        // make aliases
        $db =& sxDb::instance();
        $user_id = $request->getParameter('user_id');
        $db->qD('#_PREF_users', array('user_id' => $user_id));
        header('Location: index.php?module=Manage&action=AgentsList');
        exit;
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