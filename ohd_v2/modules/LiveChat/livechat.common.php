<?php

$xajax = new xajax("modules/LiveChat/actions/WebServiceAction.class.php");
$xajax->registerFunction("alert");
$xajax->registerFunction("SendMessage");
$xajax->registerFunction("GetOponentMessages");
$xajax->registerFunction("MakeUserTransfer");

//$xajax->registerFunction("AddFilterForm");
//$xajax->registerFunction("ClearFilters");
//$xajax->registerFunction("SaveFilter");
//$xajax->registerFunction("getLatesVersion");
//$xajax->registerFunction("updateFiles");

?>