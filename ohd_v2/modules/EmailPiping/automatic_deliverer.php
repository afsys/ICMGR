<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Deliver all new messages from pop3 account automaticaly
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 10, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);

define('BASE_DIR', realpath(dirname(__FILE__).'/../..'));
define('PEAR_PATH', BASE_DIR.'/lib/PEAR');

$separator=strpos(PHP_OS,'WIN')!==false?';':':';
ini_set('include_path', implode($separator, array(BASE_DIR, PEAR_PATH)).$separator);

require_once(BASE_DIR.'/install/db_config.php');
require_once('DB.php');

$db =& DB::connect(PEAR_DSN);
if(DB::isError($db)) die("Fatal error: could not connect to the database");
$db-> setFetchMode(DB_FETCHMODE_ASSOC);

require_once 'modules/EmailPiping/classes/ticket_delivery.inc.php';
require_once 'modules/EmailPiping/classes/email_piping_config.inc.php';

$Delivery = &new TicketDelivery($db);
$Delivery-> set_config_provider(new EmailPipingConfig($db));
$Delivery-> do_delivery();

echo '<pre>';
echo 'message:', $Delivery-> get_error_message(), "\n";
echo 'number_of_messages:', $Delivery-> get_delivered_count(), "\n";
echo '</pre>';

?>