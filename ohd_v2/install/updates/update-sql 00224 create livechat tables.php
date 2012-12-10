
CREATE TABLE #_PREF_lc_sessions (
  `sid` int(6) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL default '0',
  `opponent_id` int(11) default NULL,
  PRIMARY KEY  (`sid`)
) TYPE=MyISAM;


CREATE TABLE #_PREF_lc_conversations (
  `sid` int(11) unsigned NOT NULL default '0',
  `user_id` int(11) unsigned NOT NULL default '0',
  `message` tinytext NOT NULL,
  `service_command` varchar(50) default NULL,
  `rec_time` datetime NOT NULL default '0000-00-00 00:00:00'
) TYPE=MyISAM;


CREATE TABLE #_PREF_lc_users (
  `user_id` int(11) unsigned NOT NULL auto_increment,
  `sid` int(11) unsigned default '0',
  `ohd_user_id` int(11) default NULL,
  `user_ip` varchar(50) NOT NULL default '',
  `user_nickname` varchar(55) default NULL,
  `question` text,
  `email` varchar(100) default NULL,
  `last_action_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `user_key` varchar(50) default NULL,
  `user_action` varchar(50) default NULL,
  `closed` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) TYPE=MyISAM;
