<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OHD package.                                     |
// | Copyright (c) 2006 TSS Enterprises                                        |
// | Programming Director: Eugene Gvozdenko, ej@digitalstate.net               |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

require_once('installSubs.php');
if( !isset($_REQUEST['step']) || 
    !is_numeric($_REQUEST['step']) || 
    !in_array($_REQUEST['step'],array(1,2,3))
) {
    $step=1;
} else {
    $step=$_REQUEST['step'];
}
$error='';

if(function_exists("step$step")) call_user_func("step$step");
if($error) $step--;
if(file_exists('db_config.php'))   $step=3;
?>

<html>
<head>
<title>Welcome to Omni Help Desk Installation.</title>
<link href="install.css" rel=stylesheet>
</head>
<body bgcolor=white text=navy link=navy vlink=navy alink=navy onload="window.status='Omni Help Desk Installation Page.';" topMargin=36>
<table bgcolor=#DDDDDD cellspacing=0 cellpadding=0 border=0 align=center width=70% height=60%>
 <tr>
  <TD background="../images/h-line.gif" height=1 colspan=3><IMG height=1 src="../images/spacer.gif" width=100></TD>
 </tr>
 <tr bgcolor=#f9f9f9>
  <TD background="../images/v-line.gif" width=2 align=left><IMG src="../images/spacer.gif" width=1></TD>
  <td align=left valign=top width=100%>
   <table>
    <tr>
     <Td valign=top><img src="../images/oss-logo4.gif" border=0 alt="Omni Help Desk"></td>
     <Td width=3%> &nbsp; &nbsp;</td>
     <td valign=center>&nbsp; <img src="../images/ih.gif" border=0 alt="Omni Help Desk"></td>
    </tr>
   </table>
  </td>
  <TD background="../images/v-line.gif" width=2 align=right><IMG src="../images/spacer.gif" width=1></TD>
 </tr>
 <tr>
  <TD background="../images/v-line.gif" height=2 colspan=3><IMG height=1 src="../images/spacer.gif" width=100></TD>
 </tr>
 <tr>
  <TD background="../images/v-line.gif" width=2 align=left><IMG height=100% src="../images/spacer.gif" width=1></TD>
  <td class=main>
   <table width=100% height="100%">
    <tr>
     <td align=left valign=top><br><br><img src="../images/img01.gif" border=0 alt="The Web Protection you need!"></td>
     <td align=left valign=top class=main_part>
<?php include "step$step.html"; ?>
     </td>
    </tr>
   </table>
  </td>
  <TD background="../images/v-line.gif" height=100% width=2 align=left><IMG height=100% src="../images/spacer.gif" width=1></TD>
 </tr>
 <tr>
  <TD background="../images/h-line.gif" height=2 colspan=3><IMG height=1 src="../images/spacer.gif" width=100></TD>
 </tr>
</table>
</body>
</html>