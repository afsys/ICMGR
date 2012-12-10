<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to add product
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Nov 29, 2005
 * @version    1.00 Beta
 */
	
class ProductsAddAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		
		$ticket_product_id          = $request->getParameter('ticket_product_id');
		$ticket_product_caption     = $request->getParameter('ticket_product_caption');
		$ticket_product_desc        = $request->getParameter('ticket_product_desc');
		$ticket_product_ver_enabled = $request->getParameter('ticket_product_ver_enabled');
		$ticket_product_ver_list    = $request->getParameter('ticket_product_ver_list');
		
		
		if ($request->getParameter('product_edit'))
		{
			$errors = array();
			
			if (count($errors) == 0)
			{
				// get group_id
				if (!empty($ticket_product_id)) 
				{
					$op_name = "REPLACE";
				}
				else
				{
					$ticket_product_id = $db->getNextId('#_PREF_tickets_products', 'ticket_product_id');
					$op_name = "INSERT INTO";
				}
				
				
				$product_data = array (
					'ticket_product_id'          => $ticket_product_id,
					'ticket_product_caption'     => $ticket_product_caption,
					'ticket_product_desc'        => addslashes(htmlspecialchars(trim($ticket_product_desc))),
					'ticket_product_ver_enabled' => $ticket_product_ver_enabled,
					'ticket_product_ver_list'    => $ticket_product_ver_list,
				);
				
				$db->qI('#_PREF_tickets_products', $product_data, $op_name);
			}    
			
			header('Location: index.php?module=Manage&action=ProductsList');
			exit;
			
		}        
		
	   
		/*echo "<pre>";
		var_dump($_POST);
		var_dump($errors);
		die('ddd');*/
		
		return VIEW_SUCCESS;  
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