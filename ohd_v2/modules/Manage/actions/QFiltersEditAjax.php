<?php

require_once "../../../config.php";
require_once "../../../lib/xajax.inc.php";
require_once "../../../lib/Classes/sx_db_ini.class.php";  
require_once "../../../lib/Classes/products.class.php";
require_once "../../../lib/Classes/users.class.php";

require_once "../../../install/version.php";
require_once "../../../lib/SmartyRendererStandAlone.class.php";

function alert()
{
	$objResponse = new xajaxResponse();
	$objResponse->addAlert("123z");
	return $objResponse;
}

function AddFilterForm($index)
{
	// get smarty object
	$_smarty =  new SmartyRendererStandAlone(BASE_DIR.'/modules/Manage/templates', null, true);
	$smarty  =& $_smarty->_smarty;

	// alliases
	$db    =& sxDb::instance();
	$dbIni =  new sxDbIni($db);
	
	$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
	$smarty->assign('sys_options', $sys_options);
	
	$products = new Products($db);
	$smarty->assign('products', $products->GetDataList());
	
	// make assign to users-array
	$users = new Users();
	$at_users = $users->GetDataList();
			
	array_unshift(
		$at_users,
		array (
			'user_id'   => 0,
			'user_name' => '('. __('unassigned') .')'
		),
		array (
			'user_id'   => -1,
			'user_name' => '('. __('current_user') .')'
		)
	);
	$smarty->assign('at_users', $at_users);
	
	$smarty->assign('index', $index);
	
	// COPY TO: OHD\modules\Manage\views\QFiltersEditView_success.class.php
	
	$cnt = $smarty->fetch('qFiltersEdit-filterForm.html');
	$cnt = addcslashes($cnt, "\0..\37!@\177..\377");

	$objResponse = new xajaxResponse();
	$objResponse->addScript("
		var tbl = document.getElementById('filters_table');
		var td = tbl.insertRow(tbl.rows.length).insertCell(0);
		td.innerHTML = '$cnt';
	");
	return $objResponse;
}

function ClearFilters() {
	$db    =& sxDb::instance();
	$dbIni =  new sxDbIni($db);
	$dbIni->RemoveGroup(DB_PREF.'sys_options', 'quick_filters');
	
	$objResponse = new xajaxResponse();
	$objResponse->addScript('SaveFilters();');
	return $objResponse;
}

function SaveFilter($id, $props, $criteria = array()) {
	$objResponse = new xajaxResponse();

	// clear empty criteria
	foreach ($criteria as $k=>$v) {
		if (!isset($criteria[$k]) || 
			trim($criteria[$k]) == '' || 
			(is_array($criteria[$k]) && count($criteria[$k]) == 0)) {
			
			unset($criteria[$k]);
		}
	}
	
	// make filter array
	$filter = array (
		$props['name'] => array(
			'props'    => $props,
			'criteria' => $criteria
		)
	);
	
	$db    =& sxDb::instance();
	$dbIni =  new sxDbIni($db);
	$dbIni->SaveIni(DB_PREF.'sys_options', array('quick_filters' => $filter));
	
	return $objResponse;
}

include '../QFiltersEdit.common.php';
$xajax->processRequests();

?>