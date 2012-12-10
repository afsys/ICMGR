<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to save time tracking options.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Feb 15, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);    
require_once 'Classes/sx_db_ini.class.php';   
require_once 'Classes/timeTracker.class.php';   


    
class TicketsAddTimeTrackingAction extends Action
{
    // http://omni/ohd_new/index.php?module=Manage&action=TicketsPreferencesSave&statuses=New::,Closed::
    // http://omni/ohd_new/index.php?module=Manage&action=TicketsPreferencesSave&statuses=New:%23006600:%23003300:%23FFFF66,Closed:%230000CC:%23009999:%2366FF66,Pending::%23000099:,Open:%23009900:%2300CC00:%23FFFF33&priorities=aaaa:%23000099::,bbbbb:::&types=OSS,OHD

    function execute (&$controller, &$request, &$user)
    {
        // java script header
        header("Content-type: text/javascript");
        
        // make aliases
        $db =& sxDb::instance();
        $dbIni = new sxDbIni($db);

		// get values
		$prefs = array();
		$user_id    = $request->getParameter('user_id');
		$ticket_id  = $request->getParameter('ticket_id');
		$message_id = $request->getParameter('message_id');
		$worked     = $request->getParameter('worked');
		$chargeable = $request->getParameter('chargeable');
		$billable   = $request->getParameter('billable');
		$payable    = $request->getParameter('payable');
		$notes      = $request->getParameter('notes');
		
		//$worked = 10;
		//$chargeable = $billable = $payable = 3;


		if (empty($user_id) || empty($ticket_id))
		{
			die("alert('not enought params')");
		}
		
		$tt = new TimeTracker($user_id, $ticket_id, $message_id);
		$tt_id = $tt->AddTT($notes);
		
		if ($worked)     $tt->AddWorked($tt_id, $worked);
		if ($chargeable) $tt->AddCharged($tt_id, $chargeable);
		if ($billable)   $tt->AddBilled($tt_id, $billable);
		if ($payable)    $tt->AddPayed($tt_id, $payable);

?>
document.getElementById('requestStatus').innerHTML = 'Saved Ok!';
window.setTimeout("document.getElementById('requestStatus').innerHTML = '';", 2000);
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