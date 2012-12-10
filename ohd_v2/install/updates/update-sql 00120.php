CREATE TABLE `#_PREF_piping_accounts` (
  `acc_id` int(11) unsigned NOT NULL auto_increment,
  `acc_caption` varchar(55) default NULL,
  `acc_host` varchar(55) default NULL,
  `acc_port` int(4) default NULL,
  `acc_login` varchar(55) default NULL,
  `acc_pass` varchar(55) default NULL,
  PRIMARY KEY  (`acc_id`)
) TYPE=MyISAM;
