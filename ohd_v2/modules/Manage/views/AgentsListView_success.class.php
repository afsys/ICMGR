<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to show users list.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

require_once 'Classes/users.class.php';

class AgentsListView extends View
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
        $db =& sxDb::instance();
        
        // get users
        $users = new Users($db);
        $renderer->setAttribute('users', $users->GetDataList(0, 0, array('u.is_customer' => 0)));
        
        // user`s rights
        $user_rights = $user->getAttribute('user_rights');
        $renderer->setAttribute('user_rights', $user_rights);
                
        $renderer->setAttribute('pageBody', 'agentsList.html');
        $renderer->setTemplate('../../index.html');
        return $renderer;
    }

}
?>