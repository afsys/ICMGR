<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to add user.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

//require_once 'Classes/user.class.php';
require_once 'Classes/groups.class.php';

class GroupsEditView extends View
{
    
    /**
     * Execute the view.
     *
     * @return a Renderer instance.
     */
    function & execute (&$controller, &$request, &$user)
    {
        // alias inherited data for easy access
        $renderer =& $request->getAttribute('SmartyRenderer');
		$db =& sxDb::instance();

		// get group info on edit group
		$cat_id = $request->getParameter('cat_id');
        if (!empty($cat_id))
		{
	        // get cat data
	        $db->q("SELECT * FROM #_PREF_canned_emails_categories", array('cat_id' => $cat_id));
	        $cat = $db->fetchAssoc();
	        $renderer->setAttribute('cat',    $cat);
	        $renderer->setAttribute('cat_id', $cat_id);
		}

			
        $renderer->setAttribute('pageBody', 'groupsEdit.html');
        $renderer->setTemplate('../../index.html');
        
        return $renderer;
    }

}
?>