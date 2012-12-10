<?php

define ('UPDATE_SERVER_URL', 'http://omni/ohd_serv/srv.php');


// name
define('OHD_NAME',  'Omni-Helpdesk' );

// major version
define('OHD_VERSION_MAJOR', 2 );

// minor version
define('OHD_VERSION_MINOR', 1 );

// micro version
define('OHD_VERSION_MICRO', 1 );

// status
define('OHD_VERSION_STATUS', '-dev' );



// revision
define('OHD_REVISION', '$Rev: 363 $'); 

// revision number
define('OHD_VERSION_REVISION',  (int)(substr(OHD_REVISION, 6, strrpos(OHD_REVISION, ' ') - strpos(OHD_REVISION, ' ') - 1)));

// short version
define('OHD_VERSION', OHD_VERSION_MAJOR . '.' . OHD_VERSION_MINOR . '.' . OHD_VERSION_REVISION );

// full version
define('OHD_VERSION_FULL', OHD_VERSION_MAJOR . '.' . OHD_VERSION_MINOR . '.' . OHD_VERSION_MICRO . OHD_VERSION_STATUS);


?>