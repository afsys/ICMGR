<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to save ticket options.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Feb 15, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);    
require_once 'Classes/ticket.class.php';    
    
class TicketsOptionsSaveAction extends Action
{
    // http://omni/ohd_new/index.php?module=Tickets&action=TicketsOptionsSave&ticket_id=10&type=assigned_to&value=2&user_id=2

    function execute (&$controller, &$request, &$user)
    {
        // java script header
        header("Content-type: text/javascript");
        
        $type = $request->getParameter('type');
		if (empty($type))
		{
			echo "alert('Option type not defined.');";
			exit();
		}        
		        
        
        $value = $request->getParameter('value');
		/*
		if (!isset($_GET['value']))
		{
			echo "alert('Option value not defined.');";
			exit();
		} 
		*/     
        
        $ticket_id = $request->getParameter('ticket_id');
		if (empty($ticket_id))
		{
			echo "alert('Ticket id not defined.');";
			exit();
		}  
		
        $user_id = $request->getParameter('user_id');
		if (empty($user_id))
		{
			echo "alert('User id not defined.');";
			exit();
		}  
		
		
		$db =& sxDb::instance();
		$ticket  = new Ticket($ticket_id);
		$res = $ticket->UpdateProperty($user_id, $type, $value);
		
		
?>
//alert('Option has been saved.');
document.getElementById('opt_sel_<?= $type ?>').setAttribute('initial', '<?= $value ?>');
document.getElementById('requestStatus').innerHTML = '<?= $res ?>';
window.setTimeout(function () {
		document.getElementById('requestStatus').innerHTML = '';
		document.getElementById('opt_sel_<?= $type ?>').disabled = false;
	}
, 1000);
//document.getElementById('opt_btn_<?= $type ?>').disabled = false;
<?php
        
        return VIEW_NONE;  
    }
    
    function getDefaultView (&$controller, &$request, &$user)
    {
        return VIEW_NONE;
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