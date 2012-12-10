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
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);

require_once 'Classes/sx_db_ini.class.php';
class PreferencesEditView extends View
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
    
        // get allowed languages array
        $lang_list = getLangsList();
        $renderer->setAttribute('lang_list', $lang_list);
        
        // common system options
        $sys_options = $user->getAttribute('sys_options'); 
        $renderer->setAttribute('sys_options', $sys_options);
               
        $renderer->setAttribute('pageBody', 'preferencesEdit.html');
        $renderer->setTemplate('../../index.html');
        return $renderer;
    }
}
?>