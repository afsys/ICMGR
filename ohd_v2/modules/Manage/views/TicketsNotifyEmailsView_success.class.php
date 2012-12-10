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
 * @created    Jan 21, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);
require_once 'Classes/products.class.php';

class TicketsNotifyEmailsView extends View
{
	/**
	 * Execute the view.
	 *
	 * @return a Renderer instance.
	 */
	function & execute (&$controller, &$request, &$user)
	{
		// alias inherited data for easy access
		$renderer    =& $request->getAttribute('SmartyRenderer');
		$db          =& sxDb::instance();
		$oFCKeditor  =& $request->getAttribute('FCKeditor');
		$sys_options =  $user->getAttribute('sys_options'); 
		
		// items to get
		$edit_items = array(
			'ticket_created',
			// 'ticket_created_by_advisor',
			'ticket_renamed_by_advisor',
			'ticket_closed_by_advisor',
			'ticket_new_message',
			'ticket_group_header',
			'ticket_group_footer'
		);
		
		// get items 
		foreach ($edit_items as $edit_item) {
			// make input for message header
			$input_item_text = '<input name="prefs[emails_templates]['. $edit_item .'_subject]" value="'. $sys_options['emails_templates']["{$edit_item}_subject"] .'" type="text" style="width: 100%; margin: 0 0 4px 2px;" />';
			$renderer->setAttribute("{$edit_item}_subject", $input_item_text);

			// make fckEditor
			$oFCKeditor->Value = !empty($sys_options['emails_templates'][$edit_item]) ? $sys_options['emails_templates'][$edit_item] : '';
			$edit_item_text = $oFCKeditor->CreateFCKeditor('prefs[emails_templates]['.$edit_item.']', '100%', '200px') ;
			$renderer->setAttribute($edit_item, $edit_item_text);
		}
		
		$renderer->setAttribute('sys_options', $sys_options);
		$renderer->setAttribute('pageBody', 'ticketsNotifyEmails.html');
		$renderer->setTemplate('../../index.html');
		return $renderer;
	}
}
?>