<?php

require_once 'sx_mysql.class.php';	

class sxDB
{
	function &instance()
	{
		static $instance = false;
		if (false === $instance)
		{
			$instance = new sxMySQL(DB_HOST, DB_NAME, DB_USER, DB_PASS, 3306, DB_PREF);
		}
		return $instance;
	}
}


?>