<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show page.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    03 Jan, 2006
 * @version    1.00 Beta
 */

class HaveNoRightsView extends View
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
        
        $renderer->setAttribute('pageBody', 'haveNoRights.html');
        $renderer->setTemplate('../../index.html');

        return $renderer;
    }
}
?>
