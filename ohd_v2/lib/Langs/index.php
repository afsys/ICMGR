<?php
	
	/*
	echo "<pre>";
	var_dump(debug_backtrace());	
	trigger_error("Ddd...", E_USER_ERROR);         */
	
	//var_dump($_SESSION['attributes']['org.mojavi']);
	
	// get language value
	if (empty($_SESSION['attributes']['org.mojavi']['user_options']['defaults']['language'])) {
		if (empty($_SESSION['attributes']['org.mojavi']['sys_options']['common']['language'])) {
			
			// get language system options from database
			require_once 'Classes/sx_db.class.php';  
			require_once 'Classes/sx_db_ini.class.php';  
			$dbIni = new sxDbIni();
			$sys_options = $dbIni->LoadIni(DB_PREF.'sys_options');
			
			if (!empty($sys_options['common']['language'])) {
				$lng = $sys_options['common']['language'];
			}
			else { 
				$lng = 'en-us';
			}
		}
		else $lng = $_SESSION['attributes']['org.mojavi']['sys_options']['common']['language'];
	}
	else $lng = $_SESSION['attributes']['org.mojavi']['user_options']['defaults']['language'];
	
	// get parameters
	$lang_params = include "$lng/index.php";
	
	// load translations
	$lang  = array();
	$files = array ('index', 'tickets', 'menu', 'emailpiping', 'manage', 'strings');
	//$files = array ();
	//$files = array ('tickets');
	
	// URL: http://ohd/lib/langs/index.php
	/* define ('OHD_LIB_DIR', 'K:/Projects/Omni/ohd/trunk/lib/');
	$lng = 'fr-fr'; /**/
	
	foreach ($files as $f) {
		$fname = OHD_LIB_DIR."Langs/$lng/$f.ini";
		if (!file_exists($fname)) continue;
		
		$handle = fopen ($fname, "r");
		while (!feof ($handle)) {
		    $str = fgets($handle, 10000);
		    $aaa = explode('=', $str, 2);
		    if (count($aaa) == 2) {
		    	$lang[$aaa[0]] = strtr(trim($aaa[1]), array('\'' => '&#039;'));
		    }
		    /*echo "<pre>";
		    var_dump($aaa);*/
		}
		fclose ($handle);

		
		/*
		$ini = parse_ini_file($fname);
		if (is_array($ini)) {
			foreach ($ini as $k=>$v) {
				if (empty($v) && !empty($lang[$k])) continue;
				$lang[$k] = $v;
			}
		}
		*/
		
		/*echo "<pre>";
		var_dump($lang);
		die('a'); /**/
	}
	
	/* echo "<pre>";
	var_dump($lang);
	echo "</pre>"; */
	
	// Opened Tickets
	
	/* echo '<html><body><pre>';
	var_dump($lang);
	die(); /**/

	return array (
		'lang_params' => $lang_params,
		'lang'        => $lang
	);

?>