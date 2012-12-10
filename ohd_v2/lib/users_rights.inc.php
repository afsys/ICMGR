<?php

// PAGES ACCESS
// tickets
define('SR_TICKETS_ADD',             1);     
define('SR_TICKETS_DEL',        131072);
define('SR_TICKETS_STATISTIC',       2);     
define('SR_DOWNLOAD_EMAILS',         4);     
define('SR_EMAILS_RESP_LOGS',        8);
     

// knowledge base
define('SR_KB_MANAGE_CATS',       16);     
define('SR_KB_APPROVE_CMT',       32);     

// managing
define('SR_MNG_ANNOUNCEMENTS',    64);
define('SR_MNG_USERS',           128);
define('SR_MNG_PRODUCTS',        256);     
define('SR_MNG_GROUPS',          512);    
define('SR_MNG_CANNED_EMAILS',  1024);    

// configuration
define('SR_CONF_TICKETS',        2048);
define('SR_CONF_SYS_PREFS',      4096);
define('SR_CONF_PIPING',         8192);     

// TICKETS
define('SR_TL_VIEW_UNASSIGNED',  16384);
define('SR_TL_REASIGN',          32768);
define('SR_TL_VIEW_OTHERS',      65536);




$SR_NOTES = array (
	SR_TICKETS_ADD             => __('Allow add new ticket'),
	SR_TICKETS_DEL             => __('Allow to delete ticket'),
	SR_TICKETS_STATISTIC       => __('Allow open tickets statistic page'),
	SR_DOWNLOAD_EMAILS         => __('Allow user download emails by piping'),
	SR_EMAILS_RESP_LOGS        => __('Allow open emails responses log page'),

	SR_KB_MANAGE_CATS          => __('Allow manage KB categories and items'),
	SR_KB_APPROVE_CMT          => __('Allow approve KB comments'),
	
	SR_MNG_ANNOUNCEMENTS       => __('Allow manage announcements'),
	SR_MNG_USERS               => __('Allow manage users'),
	SR_MNG_PRODUCTS            => __('Allow manage products'),
	SR_MNG_GROUPS              => __('Allow manage departments'),
	SR_MNG_CANNED_EMAILS       => __('Allow manage canned emails'),
	
	SR_CONF_TICKETS            => __('Change ticket preferences'),
	SR_CONF_SYS_PREFS          => __('Change system preferences'),
	SR_CONF_PIPING             => __('View performance statistics for all users in system'),

	SR_TL_REASIGN              => __('Allow reasign ticket to others'),
	SR_TL_VIEW_UNASSIGNED      => __('View unassigned tickets'),
	SR_TL_VIEW_OTHERS	       => __('View tickets assigned to others users')
);


?>