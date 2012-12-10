<?
require_once("upgrade_config.php");
require_once("db_config.php");
require_once("upgradeSubs.php");
$DB = ConnectToDB();
if ($error) die($error);
$url = $DB->getOne("select url from #__OSS_config");
if (PEAR::isError($url)){
	die('error in config');
}
function CheckResult($result){
	if (trim($result) == "") return false;
	return !(strpos($result,"not found"));
}
function AddCronJobs($buffer,$cmd){
  $lines = preg_split("/\r?\n|\r/", $buffer);
  foreach ($lines as $k=>$v){
  	if (substr($v,0,1) == "#" || (strpos($v,"oss_cron") !== false) || (strpos($v,"MAILTO") !== false)) unset($lines[$k]);
  }
//  $lines[] = 'MAILTO="dinoel@gmail.com"';
  $lines[] = $cmd;
  return join("\n",$lines);
}
$url .= "cron.php?from=oss_cron";
$tests = array();
$config["fetch"] = "fetch -o /dev/null ".$url;
$config["wget"] = "wget -O /dev/null ".$url;
$config["curl"] = "curl ".$url." -o /dev/null";
//$config["curl"] = "curl ".$url;
$config["lynx"] = "lynx -dump ".$url;
if (false !== strpos(strtoupper($_ENV["OS"]),"WIN")){
	die("Crontab not avialable on windows yet (failed)");
}else{
	$CronTest = `crontab -l`;
	$FetchTest = `fetch 2>&1`;
	$CurlTest  = `curl 2>&1`;
	$WgetTest = `wget 2>&1`;
	$LynxTest = `lynx 2>&1`;
	if ($CronTest == "") $tests["cron"] = false;
	else $tests["cron"] = true;
	$tests["fetch"] = CheckResult($FetchTest);
	$tests["curl"] = CheckResult($CurlTest);
	$tests["wget"] = CheckResult($WgetTest);
	$tests["lynx"] = CheckResult($LynxTest);
}
if ($tests["cron"] == false){
	die('Crontab not avialable');
}
unset($tests["cron"]);
echo "You current crontab file:<br>";
echo nl2br($CronTest);
$command = false;
while (list($k,$v) = each($tests)){
	if ($v !== false) {
		$command = $config[$k];
		break;
	}
}
if ($command === false) die("Error! Can't run any of external program..");
$tmpfname = tempnam ("/tmp", "cronsetup"); 
$handle = fopen($tmpfname,"w");
$cmdline = "22 20 * * * ".$command;
$jobs = AddCronJobs($CronTest,$cmdline);
fwrite($handle,$jobs);
fclose($handle);
$result = exec("crontab ".$tmpfname." 2>&1");
unlink($tmpfname);
echo "<br><br>install complete, updated crontab file:<br>";
echo nl2br(`crontab -l`);
?>