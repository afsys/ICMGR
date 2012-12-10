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

require_once 'modules/EmailPiping/classes/ticket_delivery.inc.php';
require_once 'modules/EmailPiping/classes/email_piping_config.inc.php';

class DeliveryAction extends Action
{

	function execute (&$controller, &$request, &$user)
	{
		$this->db =& sxDb::instance();

		$results = TicketDelivery::MakeFullDelivery($user);
		$request->setParameter('results', $results);
		
		return VIEW_SUCCESS;
	}


	function getDefaultView (&$controller, &$request, &$user)
	{
		 return VIEW_SUCCESS;
	}

	function handleError (&$controller, &$request, &$user)
	{
		 $controller->forward(ERROR_404_MODULE, ERROR_404_ACTION);
		 return VIEW_NONE;
	}

	function registerValidators (&$validatorManager, &$controller, &$request, &$user)
	{
		 
	}

	function getPrivilege()
	{
		return null;
	}

	function isSecure()
	{
		return true;   
	}

}
?>