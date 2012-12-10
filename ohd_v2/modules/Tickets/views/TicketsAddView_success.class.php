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
 * @created    Dec 6, 2005
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'Classes/tickets.class.php';
require_once 'Classes/users.class.php';
require_once 'Classes/groups.class.php';
require_once 'Classes/products.class.php';
require_once 'Classes/sx_db_ini.class.php';

class TicketsAddView extends View
{
	
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer   =& $request->getAttribute('SmartyRenderer');
		$db         =& sxDb::instance();
		$oFCKeditor =& $request->getAttribute('FCKeditor');
		$products   =  new Products($db);
		$tickets    =  new Tickets($db);

		$is_authenticated = $user->isAuthenticated();				
		$is_customer      = $user->getAttribute('is_customer') || !$is_authenticated;

		$ticket_product_id = $request->getParameter('ticket_product_id');
		
		
		// get products list
		$products_list = $products->GetDataList();
		$renderer->setAttribute('products_list', $products_list);
		
		// make default selection
		if (!$ticket_product_id) {
			if (count($products_list)) $ticket_product_id = $products_list[0]['ticket_product_id'];
		}
		
		/*// get default options
		//$user_options = $user->getAttribute('user_options');
		$user_options =  $user->GetOptions();
		$renderer->setAttribute('defaults', $user_options['defaults']);        */
		
		// get ticket filelds list
		$ticket_id = $request->getParameter('ticket_id');
		if ($ticket_id) {
			$ticket_data = $tickets->GetItemData($ticket_id);
			$ticket_data['customer_add_emails'] = preg_replace('/\s+/', ' ', implode(', ', $ticket_data['customer_add_emails']));
			
			// $ticket_data['description_is_html'] = 1;
			if ($ticket_data['description_is_html']) {
				$oFCKeditor->Value = $ticket_data['description'];
				$description = $oFCKeditor->CreateFCKeditor('ticket_description', '420px', '200px') ;
				$renderer->setAttribute('description_area', $description);
			}
			
			$ticket_product_id = $ticket_data['ticket_product_id'];
			$renderer->setAttribute('ticket', $ticket_data);
		}
		
		// have redirect from LC form
		$lc_forward = $request->getParameter('lc_forward');
		if ($lc_forward) {
			$user_formdata = $request->getParameter('user_formdata');
			
			$ticket_data = array(
				'customer_name' => $request->getParameter('user_nickname'),
				'customer_email' => $user_formdata['email'],
				'description' => $user_formdata['question'],
			);
			
			$renderer->setAttribute('message', 'All agents are offline. Click here to submit a ticket. We will contact you ASAP. Thanks.');
			$renderer->setAttribute('ticket', $ticket_data);
		}

		// get ticket fields list
		if ($ticket_product_id) {
			$product_data = $products->GetProductData($ticket_product_id);
			// set ticket products
			$renderer->setAttribute('ticket_product_ver_list', explode("\n", $product_data['ticket_product_ver_list']));
			$renderer->setAttribute('ticket_product_ver_enabled', $product_data['ticket_product_ver_enabled']);
			
			$renderer->setAttribute('cur_product', $product_data);
			$renderer->setAttribute('ticket_product_id', $ticket_product_id);
		
			// get form items
			$db->q("
				SELECT
				   ff.ticket_field_id,
				   ff.ticket_filed_pos,
				   ff.ticket_field_caption,
				   ff.ticket_field_type,
				   ff.ticket_field_options,
				   ff.ticket_field_is_optional,
				   fv.ticket_field_value,
				   ff.show_in_userside
				FROM #_PREF_tickets_products_form_fields ff
				  LEFT JOIN #_PREF_tickets_products_forms_values fv ON
					 fv.ticket_product_id = ff.ticket_product_id
					 AND fv.ticket_field_id = ff.ticket_field_id
					 AND ticket_id = '$ticket_id'
				WHERE ff.ticket_product_id = $ticket_product_id
				ORDER BY ff.ticket_filed_pos
			");
			
			$form_items = array();
			while ($data = $db->fetchAssoc()) {
				$data['ticket_field_options'] = !empty($data['ticket_field_options']) ? unserialize($data['ticket_field_options']) : array();
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
		

		
		// assign-to users list
		$users = new Users($db);
		$renderer->setAttribute('users', $users->GetDataList());    
		
		// groups
		$groups = new Groups($db);
		$renderer->setAttribute('groups', $groups->GetDataList());              
		
		// user`s rights
		$user_rights = $user->getAttribute('user_rights');
		$renderer->setAttribute('user_rights', $user_rights);        
		
		// SYSTEM OPTIONS
		// TODO: make singleton for SYSTEM CONFIGURATION object
		//$sys_options = $user->getAttribute('sys_options');
		$dbIni = new sxDbIni($db);
		$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
		$renderer->setAttribute('sys_options', $sys_options);        

		
		// is_authenticated
		$renderer->setAttribute('is_authenticated', $is_authenticated ? 1 : 0);
		$renderer->setAttribute('is_customer', $is_customer ? 1 : 0);
		
		
		//// set statuses, priorities values
		$renderer->setAttribute('ticket_priorities', $sys_options['ticket_priorities']);
		$renderer->setAttribute('ticket_statuses',   $sys_options['ticket_statuses']);
		$renderer->setAttribute('ticket_types',   $sys_options['ticket_types']);
		
		$error_message = $request->getParameter('error_message');
		if (is_array($error_message)) {
			$renderer->setAttribute('message_caption', $error_message['caption']);
			$renderer->setAttribute('message', $error_message['message']);
		}		
		
		$renderer->setAttribute('pageBody', 'ticketsAdd.html');
		$renderer->setAttribute('is_customer',$is_customer ? 1 : 0);
		
		if ($is_customer) {
			if ($user->isAuthenticated()) $renderer->setAttribute('hide_top_panel', 1);
			$renderer->setAttribute('user_data', $user->getAttribute('user_data'));
			$renderer->setTemplate('../../user_index.html');
		}
		else {
			$renderer->setTemplate('../../index.html');
		}
		
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