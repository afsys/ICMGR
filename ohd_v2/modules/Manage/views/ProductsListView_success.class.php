<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to show products list.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'Classes/products.class.php';


class ProductsListView extends View
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
    
        // get products
        $products = new Products($db);
        $renderer->setAttribute('products', $products->GetDataList());
        $renderer->setAttribute('pageBody', 'productsList.html');
        
        $renderer->setTemplate('../../index.html');
        return $renderer;
    }

    /**
    * There's no cleanup to do for this view.
    *
    function cleanup ()
    {

    } */
}
?>