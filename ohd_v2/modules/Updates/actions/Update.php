<?php

require_once ("../../../lib/xajax.inc.php");
require_once ("../../../lib/Classes/sx_updater.class.php");
require_once ("../../../lib/PEAR/Tar.php");
require_once ("../../../install/version.php");


function alert()
{
	$objResponse = new xajaxResponse();
	$objResponse->addAlert("123z");
	return $objResponse;
}

function getLatesVersion()
{
	$objResponse = new xajaxResponse();
	$ver = file_get_contents(UPDATE_SERVER_URL. '?action=getLatestVerion');
	$objResponse->addAssign('latestVersion', 'innerHTML', $ver);
	$objResponse->addAssign('update_btn', 'disabled', false);
	
	return $objResponse;
}

function updateFiles($cur_step)
{
	function addLog($text, $status) {
		$res = "";
		if ($text !== null) {
			$res .= "$text ";
		}
		if ($status !== null) {
			switch ($status) {
				case 'ok':
					$res .= "<span style=\"color: green; font-weight: bold;\">Ok!</span><br />";
					break;
				case 'error':
					$res .= "<span style=\"color: red; font-weight: bold;\">Error!</span><br />";
					break;
				default:
					$res .= "<span style=\"font-weight: bold;\">$status</span><br />";
					break;
			}
		}
		return $res;
	}
	
	$objResponse = new xajaxResponse();
	$updater = new sxUpdater();

	switch ($cur_step) {
		// START UPDATE
		case 'start':
			$objResponse->addAssign('update_log', 'innerHTML', addLog('Started Update...', ''));
			$objResponse->addScript('xajax_updateFiles("step1")');
			break;
		
		// DOWNLOAD FILES
		case 'step1':
			$objResponse->addAppend('update_log', 'innerHTML', addLog('Downloading Files...',null));
			$objResponse->addScript('xajax_updateFiles("step1e")');
			break;
			
		case 'step1e':
			// download
			$res = $updater->DownloadFile(UPDATE_SERVER_URL. '?action=getFiles', 'updatedata.gz');
			
			// result
			$objResponse->addAppend('update_log', 'innerHTML', addLog(null, $res ? 'ok' : 'error'));
			if (!$res) {
				$objResponse->addAlert($updater->error_str);
				$objResponse->addAppend('update_log', 'innerHTML', addLog($updater->error_str, ''));
			}
			$objResponse->addScript('xajax_updateFiles("'. ($res ? 'step2' : 'abort') .'")');
			break;

		// EXTRACT FILES
		case 'step2':
			$objResponse->addAppend('update_log', 'innerHTML', addLog('Replacing Files...',null));
			$objResponse->addScript('xajax_updateFiles("step2e")');
			break;
			
		case 'step2e':
			// unzip
			$res = $updater->UnzipFiles('updatedata.gz', 'files/');

			// result
			$objResponse->addAppend('update_log', 'innerHTML', addLog(null, $res ? 'ok' : 'error'));
			if (!$res) {
				$objResponse->addAlert($updater->error_str);
				$objResponse->addAppend('update_log', 'innerHTML', addLog($updater->error_str, ''));
			}
			$objResponse->addScript('xajax_updateFiles("'. ($res ? 'end' : 'abort') .'")');
			break;
			
		// FINISH UPDATE
		case 'end':
			$objResponse->addAppend('update_log', 'innerHTML', '<div style="width: 250px; border-top: 1px solid silver; padding-top: 3px; margin-top: 5px;"></div>');
			$objResponse->addAppend('update_log', 'innerHTML', addLog('Update Completed!', ''));
			break;
			
		case 'abort':
			$objResponse->addAppend('update_log', 'innerHTML', '<div style="width: 250px; border-top: 1px solid silver; padding-top: 3px; margin-top: 5px;"></div>');
			$objResponse->addAppend('update_log', 'innerHTML', addLog('Update Aborted because of error!', ''));
			break;

			
		default:
			break;
	}
	
	return $objResponse;
}

include '../update_common.php ';
$xajax->processRequests();

?>