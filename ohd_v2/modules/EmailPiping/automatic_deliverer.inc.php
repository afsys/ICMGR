<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Include file with automatic delivery
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 10, 2006
 * @version    1.00 Beta
 */

function automatic_delivery(&$db)
{
     require_once 'modules/EmailPiping/classes/ticket_delivery.inc.php';
     require_once 'modules/EmailPiping/classes/email_piping_config.inc.php';

     $Delivery = &new TicketDelivery($db);
     $Delivery->set_config_provider(new EmailPipingConfig($db));
     $Delivery->do_delivery();
}
?>