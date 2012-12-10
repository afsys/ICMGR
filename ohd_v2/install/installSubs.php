<?php

// +---------------------------------------------------------------------------+
// | This file is part of the OHD package.                                     |
// |                                                                           |
// | For the full copyright and license information, please view the           |
// | COPYRIGHT file that was distributed with this source code.                |
// +---------------------------------------------------------------------------+

require_once 'version.php';

$prefix='';
function step3(){
    global $error,$prefix;

    if(isset($_POST['noReg'])){
        return;
    }
    ini_set('include_path','../lib/PEAR/');
    include 'DB.php';
		if (!file_exists("db_config.php")){
	    $user = $_POST['dbuser'];
	    $pass = $_POST['dbpass'];
	    $host = $_POST['dbhost'];
	    $db_name = $_POST['dbname'];
	    $type=$_POST['dbtype'];
	    $prefix=$_POST['prefix'];
	  }else{
	  	include("db_config.php");
	  	extract($dbConfig);
	  }
	  if (trim($db_name) == ""){
	  	$error = 'Database name cannot be empty!';
	  	return;
	  }
    // Data Source Name: This is the universal connection string
    $dsn = "$type://$user:$pass@$host/$db_name";

    // DB::connect will return a PEAR DB object on success
    // or an PEAR DB Error object on error

    $db = DB::connect($dsn);

    // With DB::isError you can differentiate between an error or
    // a valid connection.
    if (DB::isError($db)) {
        $msg=$db->userinfo;
        if(strpos($msg,'Access denied for user')){
            $error='Acces is denied for this user';
        }elseif(strpos($msg,'Unknown database')){
            $dsn = "$type://$user:$pass@$host/";
            $db=DB::connect($dsn);
            $res=$db->query('create database '.$db_name);
            if($db->isError($res))
                $error="Selected database - <b>db_name</b> - does not exist, and cannot be created. Please create it manually and continue the installation after that.";
            else
                $db=DB::connect($dsn.$db_name);
        } else $error='There has been an error while connecting to the database. Please check all settings';
        if ($error)
            return;
    }
    
		// make config file content
		$fileCont = file_get_contents('db_config.php.exm');
		$fileCont = str_replace('%DB_HOST%', $host, $fileCont);
		$fileCont = str_replace('%DB_USER%', $user, $fileCont);
		$fileCont = str_replace('%DB_PASS%', $pass, $fileCont);
		$fileCont = str_replace('%DB_NAME%', $db_name, $fileCont);
		$fileCont = str_replace('%DB_PREF%', $prefix, $fileCont);



    if(!$fh = fopen('db_config.php', 'w')) {
        $error = "Can't write config file. Please change permissions or upload file manually";
        return;
    }
    fputs($fh,$fileCont);
    fclose($fh);
  
    populate_db($db, 'ohd.sql');
    populate_db($db, 'lc.sql');
    populate_db($db, 'kb.sql');
    //$db->query('UPDATE '.$prefix."config set url='".'http://'.$_SERVER['HTTP_HOST'].dirname(dirname($_SERVER['PHP_SELF']))."/'");
    //$db->query('UPDATE '.$prefix."users set signup_ip='".$_SERVER['REMOTE_ADDR']."', signup_date='".date("Y-m-d")."' WHERE id=1");
    
    $db->query("INSERT INTO {$prefix}sys_options VALUES ('system', 'install_version', 1, ". OHD_VERSION_REVISION .", 0)");

/*    
    // Populate Login, Signup, and Payment form fields in config table with Default template data in text files.
    $oss_base_path = dirname(__FILE__);

    $formTemplatePath = $oss_base_path . '/defaultlogin.html';
    $loginFormData = fread(fopen($formTemplatePath, "r"), filesize($formTemplatePath));
    $formTemplatePath = $oss_base_path . '/defaultsignup.html';
    $signupFormData = fread(fopen($formTemplatePath, "r"), filesize($formTemplatePath));
    $formTemplatePath = $oss_base_path . '/defaultpayment.html';
    if (filesize($formTemplatePath) != 0){
	    $paymentFormData = fread(fopen($formTemplatePath, "r"), filesize($formTemplatePath));
	  }else{
	  	$paymentFormData = "";
	  }
	  $passConfirmPath = $oss_base_path .'/defaultpassconfirm.html';
	  $passConfirmData = fread(fopen($passConfirmPath, "r"), filesize($passConfirmPath));
	  
	  $oss_path = "/".dirname(dirname($_SERVER['PHP_SELF']))."/";
	  $oss_path = str_replace("//","/",$oss_path);
	  
		$signupFormData = str_replace("!url!",$oss_path,$signupFormData);
    $res = $db->query("update ".$prefix."config set login_form=".$db->quote($loginFormData));
    $res = $db->query("update ".$prefix."signup_forms set HtmlCode=".$db->quote($signupFormData));
*/    
    $db->disconnect();
}


function populate_db(&$db, $sqlfile = 'odh.sql') {
    global $prefix;
	$query = fread(fopen($sqlfile, "r"), filesize($sqlfile));
	$pieces  = split_sql($query);
	
	/*echo "<pre>";
	var_dump($pieces);*/
	
	$errors = array();
	for ($i=0; $i < count($pieces); $i++) {
		$pieces[$i] = trim($pieces[$i]);
		if(!empty($pieces[$i]) && $pieces[$i] != "#") 
		{
			$res = $db->query(str_replace('#PREF_', $prefix, $pieces[$i]));
			if (DB::isError($res)) {
				echo "<pre>";
				var_dump($res);
				die();
			}
		}
	}
}

function split_sql($sql) {
	$sql = trim($sql);
	$sql = ereg_replace("\n#[^\n]*\n", "\n", $sql);

	$buffer = array();
	$ret = array();
	$in_string = false;

	for($i=0; $i<strlen($sql)-1; $i++) {
		if($sql[$i] == ";" && !$in_string) {
			$ret[] = substr($sql, 0, $i);
			$sql = substr($sql, $i + 1);
			$i = 0;
		}

		if($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
			$in_string = false;
		}
		elseif(!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
			$in_string = $sql[$i];
		}
		if(isset($buffer[1])) {
			$buffer[0] = $buffer[1];
		}
		$buffer[1] = $sql[$i];
	}

	if(!empty($sql)) {
		$ret[] = $sql;
	}
	return($ret);
}

?>
