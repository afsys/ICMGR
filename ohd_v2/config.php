<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Mojavi package.                                  |
// | Copyright (c) 2003 Sean Kerr.                                             |
// |                                                                           |
// | For the full copyright and license information, please view the COPYRIGHT |
// | file that was distributed with this source code. If the COPYRIGHT file is |
// | missing, please visit the Mojavi homepage: http://www.mojavi.org          |
// +---------------------------------------------------------------------------+

// ----- FILE-SYSTEM DIRECTORIES -----
//file-system path where OHD is installed
define('BASE_DIR', realpath(dirname(__FILE__)).'/');
//file-system path where help ticket attachments are stored -- may be changed for extra security
define('ATTACHMENT_DIR', BASE_DIR.'attachments/');

$dir = '';
if (substr($_SERVER['PHP_SELF'],-1) != '/') 
{
     $dir=@dirname($_SERVER['PHP_SELF']);
}

if (!$dir) 
{
     $dir=@dirname($_SERVER['PHP_SELF']);
}

if ($dir)
{
     $dir.='/';
}

if ($dir == '//')
{
     $dir='';
}

if (!empty($_SERVER['PATH_TRANSLATED']) && $_SERVER['PATH_TRANSLATED'] != "")
{
     $_SERVER['SCRIPT_FILENAME']=$_SERVER['PATH_TRANSLATED'];
}

//url path to OHD installation (e.g. /ohd/)
define('BASE_URL', str_replace('\/', '/', $dir));

/**
 * An absolute file-system path to the log directory.
 *
 * Note: This directory must be writable by any user.
 */
define('LOG_DIR', BASE_DIR . 'logs/');

/**
 * An absolute file-system path to the all-in-one class file Mojavi
 * uses.
 */
define('MOJAVI_FILE',    BASE_DIR.'mojavi-all-classes.php');
define('MOJAVI_FILE_EX', BASE_DIR.'mojavi-ohd-classes.php');

/**
 * An absolute file-system path to the optional classes directory.
 */
define('OPT_DIR', BASE_DIR.'opt/');


// ----- WEB DIRECTORIES AND PATHS -----


$dir='';
if(substr($_SERVER['PHP_SELF'],-1)!='/') $dir=@dirname($_SERVER['PHP_SELF']);
if(!$dir) $dir=@dirname($_SERVER['PHP_SELF']);
if($dir) $dir.='/';
if($dir=='//') $dir='';
//echo $dir;
if(isset($_SERVER["PATH_TRANSLATED"]) && $_SERVER['PATH_TRANSLATED'] != "")
 $_SERVER['SCRIPT_FILENAME']=$_SERVER['PATH_TRANSLATED'];
define('WEB_MODULE_DIR', str_replace('\/', '/', $dir));

// ----- FCK SETTINGS -----
define('FCKeditor_DIR',             WEB_MODULE_DIR.'lib/FCKeditor/');
define('JSCook_DIR',                WEB_MODULE_DIR.'js/JSCookMenu/');
define('PEAR_DB_DIR',               WEB_MODULE_DIR.'lib/PEAR/');



/**
 * An absolute web path to the index.php script.
 */
define('SCRIPT_PATH', 'index.php');


// ----- ACCESSOR NAMES -----

/**
 * The parameter name used to specify a module.
 */
define('MODULE_ACCESSOR', 'module');

/**
 * The parameter name used to specify an action.
 */
define('ACTION_ACCESSOR', 'action');


// ----- MODULES AND ACTIONS -----


/**
 * The action to be executed when an unauthenticated user makes a request for
 * a secure action.
 */
define('AUTH_MODULE', 'System');
define('AUTH_ACTION', 'AuthenticateUser');

/**
 * The action to be executed when a request is made that does not specify a
 * module and action.
 */
define('DEFAULT_MODULE', AUTH_MODULE);
define('DEFAULT_ACTION', AUTH_ACTION);

/**
 * The action to be executed when a request is made for a non-existent module
 * or action.
 */
define('ERROR_404_MODULE', 'System');
define('ERROR_404_ACTION', 'PageNotFound');

/**
 * The action to be executed when an authenticated user makes a request for
 * an action for which they do not possess the privilege.
 */
define('SECURE_MODULE', 'Tickets');
define('SECURE_ACTION', 'TicketsListOpened');

/**
 * The action to be executed when the available status of the application
 * is unavailable.
 */
define('UNAVAILABLE_MODULE', 'System');
define('UNAVAILABLE_ACTION', 'SystemDown');


// ----- MISC. SETTINGS -----


/**
 * Whether or not the web application is available or if it's out-of-service
 * for any reason.
 */
define('AVAILABLE', TRUE);

/**
 * Should typical PHP errors be displayed? This should be used only for
 * development purposes.
 *
 * 1 = on, 0 = off
 */
define('DISPLAY_ERRORS', 1);

/**
 * The associative array that may contain a key that holds path information
 * for a request, and the key name.
 *
 * 1 = $_SERVER array
 * 2 = $_ENV array
 *
 * Note: This only needs set if URL_FORMAT = 2.
 */
define('PATH_INFO_ARRAY', 1);
define('PATH_INFO_KEY',   'PATH_INFO');

/**
 * The format in which URLs are generated.
 *
 * 1 = GET format
 * 2 = PATH format
 *
 * GET  format is ?key=value&key=value
 * PATH format is /key/value/key/value
 *
 * Note: PATH format may required modifications to your webserver configuration.
 */
define('URL_FORMAT', 1);

/**
 * Should we use sessions?
 */
define('USE_SESSIONS', TRUE);

//local libraries
define('OHD_LIB_DIR', BASE_DIR.'lib/');

//Smarty setup
define('SMARTY_DIR',                    BASE_DIR.'lib/Smarty/');
define('SMARTY_CACHE_DIR',              SMARTY_DIR.'cached/');
define('SMARTY_COMPILE_DIR',            BASE_DIR.'_tmp/template_c/');
define('SMARTY_PLUGINS_DIR',            SMARTY_DIR.'plugins/');
define('SMARTY_DEBUG_TPL',              SMARTY_DIR.'debug.tpl');
define('DEBUGGING_CTRL',                'NONE');
define('SMARTY_DEBUGGING',              FALSE);
define('SMARTY_CACHING',                FALSE);
define('SMARTY_FORCE_COMPILE',          TRUE);
define('SMARTY_ERROR_REPORTING', E_WARNING);

require_once SMARTY_DIR.'Smarty.class.php';

define('FCKEDITOR_DIR', BASE_URL.'lib/FCKeditor/');

/**
 * Should we use key validation?
 */
define('USE_KEY_VALIDATION', TRUE);


// ----- PEAR SETTINGS -----
//Include files necessary for DB

define('PEAR_DIR',             OHD_LIB_DIR.'PEAR/');
$separator=strpos(PHP_OS,'WIN')!==false?';':':';

ini_set('include_path', implode($separator, array(BASE_DIR, OHD_LIB_DIR, PEAR_DIR)));

if(!is_readable(BASE_DIR.'install/db_config.php'))
{
    header("Location: ".BASE_URL."install/index.php");
    exit;
}


// ADD ADDITONAL CONFIGURATION DATA
require_once BASE_DIR.'install/db_config.php';

//include any other necessary classes
require_once 'lib/common.functions.php';
require_once 'lib/rights.inc.php';
require_once 'lib/users_rights.inc.php';

// add event hadler
$local_site = true;
require_once BASE_DIR.'lib/Classes/sx_error_handler.class.php';
require_once BASE_DIR.'lib/Classes/sx_db.class.php';





?>