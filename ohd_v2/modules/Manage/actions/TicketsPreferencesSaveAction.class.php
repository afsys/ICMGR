<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OSS package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

/**
 * Implements action necessary to save ticket preferences.
 *
 * @author     Konstantin Gorbachov <slyder@bk.ru>
 * @created    Jan 5, 2006
 * @version    1.00 Beta
 */

error_reporting(E_ALL);    
require_once 'Classes/sx_db_ini.class.php';    
    
class TicketsPreferencesSaveAction extends Action
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

		// save prefs
		$prefs = array();

		// statuses
		$statuses = $request->getParameter('statuses');
		if (!empty($statuses))
		{
			$dbIni->removeGroup(DB_PREF.'sys_options', 'ticket_statuses');
			$statuses = explode(",", $statuses);
			foreach ($statuses as $k=>$v) 
			{
				list ($name, $textcolor, $bordercolor, $bgcolor) = explode(':', $v);
				$data  = array (
					'textcolor'   => $textcolor,
					'bordercolor' => $bordercolor,
					'bgcolor'     => $bgcolor
				);
				$prefs['ticket_statuses'][$name] = $data;
				//$prefs['ticket_statuses'][$name] = '123';
			}
		}
		
		/*echo "/ *";
		var_dump($prefs);
		echo "* /"; /**/
		
		// priorities
		$priorities = $request->getParameter('priorities');
		if (!empty($priorities))
		{
			$dbIni->removeGroup(DB_PREF.'sys_options', 'ticket_priorities');
			$priorities = explode(",", $priorities);
			foreach ($priorities as $k=>$v) 
			{
				list ($name, $textcolor, $bordercolor, $bgcolor) = explode(':', $v);
				$data  = array (
					'textcolor'   => $textcolor,
					'bordercolor' => $bordercolor,
					'bgcolor'     => $bgcolor
				);
				$prefs['ticket_priorities'][$name] = $data;
			}
		}	
		
		/*echo "/ *";
		var_dump($prefs);
		echo "* /"; /**/
		
		// types
		$types = $request->getParameter('types'); 
		if (!empty($types))
		{
			$dbIni->removeGroup(DB_PREF.'sys_options', 'ticket_types');
			$types = explode(",", $types);
			foreach ($types as $k=>$v) $prefs['ticket_types'][$v] = null;
		}
		
		// status for new
		$status_for_new = $request->getParameter('status_for_new'); 
		if (!empty($status_for_new))
		{
			if (empty($prefs['tickets'])) $prefs['tickets'] = array();
			$prefs['tickets']['status_for_new'] = $status_for_new;
		}

		// status for closed
		$status_for_closed = $request->getParameter('status_for_closed'); 
		if (!empty($status_for_closed))
		{
			if (empty($prefs['tickets'])) $prefs['tickets'] = array();
			$prefs['tickets']['status_for_closed'] = $status_for_closed;
		}
		

		// status for reopened
		$status_for_reopened = $request->getParameter('status_for_reopened'); 
		if (!empty($status_for_reopened))
		{
			if (empty($prefs['tickets'])) $prefs['tickets'] = array();
			$prefs['tickets']['status_for_reopened'] = $status_for_reopened;
		}
		
		// status for unanswered
		$status_for_unanswered = $request->getParameter('status_for_unanswered');
		if (!empty($status_for_unanswered))
		{
			if (empty($prefs['tickets'])) $prefs['tickets'] = array();
			$prefs['tickets']['status_for_unanswered'] = $status_for_unanswered;
		}

		// priority_for_new
		$priority_for_new = $request->getParameter('priority_for_new');
		if (!empty($priority_for_new))
		{
			if (empty($prefs['tickets'])) $prefs['tickets'] = array();
			$prefs['tickets']['priority_for_new'] = $priority_for_new;
		}


		
		if (count($prefs) > 0) $dbIni->saveIni(DB_PREF.'sys_options', $prefs);
		
		// recashing system options
		$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
		$user->setAttribute('sys_options', $sys_options);

?>
document.getElementById('requestStatus').innerHTML = 'Saved Ok!';
window.setTimeout("document.getElementById('requestStatus').innerHTML = '';", 2000);
document.getElementById('submit_btn').disabled = false;
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