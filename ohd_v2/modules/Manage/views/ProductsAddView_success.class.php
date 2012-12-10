<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to add product.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'Classes/products.class.php';

class ProductsAddView extends View
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
        $ticket_product_id = $request->getParameter('product_id');
        if (!empty($ticket_product_id))
        {
	        // get product
	        $products = new Products($db);
	        $renderer->setAttribute('ticket_product_id', $ticket_product_id);
	        $renderer->setAttribute('product', $products->GetProductData($ticket_product_id));        	
        }
        else
            $privileges = array();
            
        $renderer->setAttribute('pageBody', 'productsAdd.html');
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