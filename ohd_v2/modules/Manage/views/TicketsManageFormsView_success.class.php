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

require_once 'Classes/users.class.php';
require_once 'Classes/products.class.php';

class TicketsManageFormsView extends View
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
		$products = new Products($db);
		
		$ticket_product_id = $request->getParameter('ticket_product_id');
		
		// get products list
		$products_list = $products->GetDataList();
		$renderer->setAttribute('products_list', $products_list);

		// get ticket filelds list
		if ($ticket_product_id)
		{
			$product_data = $products->GetProductData($ticket_product_id);
			$renderer->setAttribute('cur_product', $product_data);
			$renderer->setAttribute('ticket_product_id', $ticket_product_id);
		
			// get form items
			$db->q("
				SELECT 
				   ticket_field_id,
				   ticket_filed_pos,
				   ticket_field_caption,
				   ticket_field_type,
				   ticket_field_options,
				   ticket_field_is_optional,
				   show_in_userside
				FROM #_PREF_tickets_products_form_fields
				WHERE ticket_product_id = $ticket_product_id
				ORDER BY ticket_filed_pos
			");
			
			$form_items = array();
			while ($data = $db->fetchAssoc()) 
			{
				$options = !empty($data['ticket_field_options']) ? unserialize($data['ticket_field_options']) : array();;
				$data['ticket_field_options'] = is_array($options) ? $options : null;
				$form_items[] = $data;
			}
			$renderer->setAttribute('form_items', $form_items);
			
			$db->q("
				SELECT IFNULL(MAX(ticket_field_id), 0) + 1
				FROM #_PREF_tickets_products_form_fields
				WHERE ticket_product_id = $ticket_product_id
			");
			$val = $db->result();
			$renderer->setAttribute('form_item_index', $val);
			
		}
		
		// items types
		$items_types = array (
			array ('type' => 'input',        'caption' => 'Input field', 'multiple' => 0),
			array ('type' => 'textarea',     'caption' => 'Text area',   'multiple' => 0),
			array ('type' => 'check',        'caption' => 'Checkbox',    'multiple' => 0),
			array ('type' => 'select',       'caption' => 'Select',      'multiple' => 1),
			array ('type' => 'multiselect',  'caption' => 'Multiselect', 'multiple' => 1),
		);
		$renderer->setAttribute('items_types', $items_types);
		
		// default assign-to users list
		$users = new Users($db);
		$renderer->setAttribute('users', $users->GetDataList());    
		
				
		$renderer->setAttribute('pageBody', 'ticketsManageForms.html');
		$renderer->setTemplate('../../index.html');
		return $renderer;
	}
}
?>