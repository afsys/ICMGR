<?php
include("../config.php");
include("Classes/userKb.class.php");
$ukb = new UserKB();
$ukb->process_request($kb_form);
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title>Omni Help Desk</title>
	<link href="../styles.css" rel="stylesheet" type="text/css">
	<link href="../emails.css" rel="stylesheet" type="text/css">
	<link href="../new.css" rel="stylesheet" type="text/css">
</head>
<body>
	<div style="width: 800px; margin: auto;"><?= $kb_form ?></div>
</body>
</head>