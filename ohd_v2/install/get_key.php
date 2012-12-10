<?
 ini_set('include_path','../lib/PEAR/');
 include 'DB.php';
	include("db_config.php");
	extract($dbConfig);
 // Data Source Name: This is the universal connection string
 $dsn = "$type://$user:$pass@$host/$db";
 // DB::connect will return a PEAR DB object on success
 // or an PEAR DB Error object on error
 $db = DB::connect($dsn);
function step4(){
	global $error,$db;
 	//$res = $db->query("select * from #__OSS_config");
 	if (true) {
     include("../lib/keyChecker.class.php");
     $kc = new keyChecker("http://www.omnihelpdesk.com/ohd_licenser/index.php");
     $kc_result = $kc->install();
 		if ($kc_result !== true){
 			$error = $kc_result;
 			return false;
 		}else{
 			$response = $kc->getResponse();
 			$lic_key = $response["license_key"];
 			//$r = $db->query("update #__OSS_config set license_key=".$db->quote($lic_key));
 			$r = $db->query("replace into ".DB_PREF."config_string(name,value) values ('license_key',".$db->quote($lic_key).")");
 		}
 	}else{
 		$error = "config table is empty, try to reinstall again";
 		return false;
 	}
 	return true;
}
$lic_key = $db->getOne("select value from ".DB_PREF."config_string where name='license_key'");
$generated = false;
if ($lic_key == "" || @$_GET["force"] == 1) {
	$result = step4();
	if ($result == true) $generated = true;
}
?>
<html>
<head>
<title>Welcome to Omni Helpdesk Installation.</title>
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


<Table cellpadding=5>
 <tr class=main_part>
  <td align=left class=msg>
  <?php 
   global $error;
   if (!$error) {?>
<div align=left class=notify><img src="../images/squares.gif" border=0 alt=""> &nbsp;<u>Installation</u> -> Get license key</div>
<br>
<div align=left class=title>Omni Help Desk Install Wizard</div>
<br>
<div style="text-align: justify;">
<? if (!$generated) { ?>
You have already get you license key and do not need to run install anymore!
You should now be able to login to the OSS Administration Page and setup all other features from there.<br>
If you can't login because license key error you can try to get key again :<br>
<center><input type="submit" name="noGen" class=button value="Get license key again" onClick="location.href='get_key.php?force=1';" class=submit></center>
<?} else { ?>
You have successfully get license key.
You should now be able to login to the OSS Administration Page and setup all other features from there.<br>
<? }?>
<br>
</div>
  </td>
 </tr>
 <tr>
  <td align=center class=notify>
<b>Default username and password is 'admin' (without quotes). &nbsp;Click to <a href=../index.php>Login</a><br></b>
<? }else {
?>
<div align=left class=notify><img src="../images/squares.gif" border=0 alt=""> &nbsp;<u>Installation</u> -> Get license key</div>
<?
echo "<font color=red><center>Error!</center> <br> ".$error."</font><br><br><center><a href='get_key.php'>Try again</a></center>"; }?>
  </td>
 </tr>
</table>
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