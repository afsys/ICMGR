<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to show tickets list
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Dec 5, 2005
 * @version    1.00 Beta
 */
	
error_reporting(E_ALL);
	
class TicketsManageFormsAction extends Action
{
	function execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db =& sxDb::instance();
		
		$is_form_items_submit = $request->getParameter('is_form_items_submit');
		$ticket_product_id    = $request->getParameter('ticket_product_id');
		
		if ($is_form_items_submit && $ticket_product_id)
		{
			// DELETE ITEMS
			$deleted_fileds_ids = $request->getParameter('deleted_fileds_ids');
			// filter value for numeric items
			$deleted_fileds_ids = explode(',', $deleted_fileds_ids);
			foreach ($deleted_fileds_ids as $k=>$v) if (!is_numeric($v)) unset($deleted_fileds_ids[$k]);
			if (count($deleted_fileds_ids) > 0)
			{
				$deleted_fileds_ids = implode(',', $deleted_fileds_ids);
				$db->q("DELETE FROM #_PREF_tickets_products_forms_values WHERE ticket_product_id = $ticket_product_id AND ticket_field_id IN ($deleted_fileds_ids)");
				$db->q("DELETE FROM #_PREF_tickets_products_form_fields  WHERE ticket_product_id = $ticket_product_id AND ticket_field_id IN ($deleted_fileds_ids)");
			}
			
			// UPDATE ITEMS
			$items = $request->getParameter('item');
			if (is_array($items))
			{
				$field_pos = 1;
				foreach ($items as $item)
				{
					// add subitems
					if (!empty($item['subitems']) && is_array($item['subitems']))
					{
						foreach ($item['subitems'] as $k=>$v) if ($v == '') unset($item['subitems'][$k]);
						$ticket_field_options = serialize($item['subitems']);
					}
					else $ticket_field_options = null;
					
					$field_data = array (
						'ticket_filed_pos'         => $field_pos,
						'ticket_field_caption'     => $item['capt'],
						'ticket_field_type'        => $item['type'],
						'ticket_field_options'     => $ticket_field_options,
						'ticket_field_is_optional' => empty($item['isopt']) ? 1 : 0,
						'show_in_userside'         => empty($item['show_in_userside']) ? 0 : 1
					);
					
					// new item - insert
					if (empty($item['ticket_field_id']))
					{
						$ticket_field_id = $db->getNextId('#_PREF_tickets_products_form_fields', 'ticket_field_id', "ticket_product_id = $ticket_product_id");
						$field_data['ticket_field_id']   = $ticket_field_id;
						$field_data['ticket_product_id'] = $ticket_product_id;
						$db->qI('#_PREF_tickets_products_form_fields', $field_data);
					}
					// item exists - update
					else
					{
						$where = array (
							'ticket_field_id'   => $item['ticket_field_id'],
							'ticket_product_id' => $ticket_product_id
						);
						$db->qI('#_PREF_tickets_products_form_fields', $field_data, 'UPDATE', $where);
					}
					
					$field_pos++;
				}
				
				/*echo "<pre>";
				var_dump($_POST);
				echo "</pre>";
				die('ggggggg'); /**/
			}
			else
			{
				//die('E:\Projects\Omni\ohd_new\modules\Manage\actions\TicketsManageFormsAction.class.php:38');
			}
			
			// update redirect url
			$db->qI('#_PREF_tickets_products', array (
						'ticket_product_desc'           => $request->getParameter('ticket_product_desc'),
						'ticket_product_redirect_url'   => $request->getParameter('ticket_product_redirect_url'),
						'ticket_product_email_customer' => $request->getParameter('ticket_product_email_customer'),
						'default_tech'                  => $request->getParameter('default_tech')),
					'UPDATE',
					array ('ticket_product_id' => $ticket_product_id)
				);
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