<?php
	
// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Deliver all messages from pop3 account.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 10, 2006
 * @version    1.00 Beta
 */

class DeliveryView extends View
{
	function & execute (&$controller, &$request, &$user)
	{
		$renderer = & $request->getAttribute('SmartyRenderer');     

		$results = $request->getParameter('results');

		$renderer->setAttribute('results', $results);
		$renderer->setAttribute('pageBody', 'Delivery.html');
		$renderer->setTemplate('../../index.html');

		return $renderer;
	}

}
?>