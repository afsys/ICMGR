<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements view necessary to ...
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Aug 03, 2006
 * @version    1.00 Beta
 */
 
class TestView extends View
{
	function & execute (&$controller, &$request, &$user)
	{
		// make aliases
		$db       =& sxDb::instance();
		$renderer =& $request->getAttribute('SmartyRenderer');
		
		$param  = $request->getParameter('param');
		
		
		
		$renderer->setAttribute('ticket_id',  $ticket_id);
		$renderer->setAttribute('message_id', $message_id);
		
		$renderer->setAttribute('pageBody', 'clean_ticket_message.html');
		$renderer->setTemplate('../../index.html');
		
		return $renderer;
	}
}

?>
