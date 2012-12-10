CREATE TABLE `#PREF_lc_footprints` (
  `fp_id` int(11) unsigned NOT NULL auto_increment,
  `fp_url` text,
  `fp_user_ip` int(11) default NULL,
  `fp_rec_date` datetime default NULL,
  `fp_session_id` varchar(32) default NULL,
  PRIMARY KEY  (`fp_id`)
);

CREATE TABLE `#PREF_lc_messages` (
  `message_id` int(11) NOT NULL auto_increment,
  `sid` int(11) unsigned NOT NULL default '0' COMMENT 'Session Id-num',
  `user_id` int(11) unsigned NOT NULL default '0' COMMENT 'User` id posted current message',
  `message` text NOT NULL,
  `message_params` text COMMENT 'Additional serialized message options',
  `service_command` varchar(50) default NULL COMMENT 'String code for system command. For ex: user_logged_in, ...',
  `rec_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`message_id`)
);

CREATE TABLE `#PREF_lc_pred_responses` (
  `resp_id` int(11) unsigned NOT NULL auto_increment,
  `resp_caption` varchar(255) NOT NULL default '',
  `resp_body` text NOT NULL,
  PRIMARY KEY  (`resp_id`)
);

CREATE TABLE `#PREF_lc_sessions` (
  `sid` int(6) unsigned NOT NULL auto_increment,
  `group_id` int(11) NOT NULL default '0' COMMENT 'Department id',
  `created_at` datetime default NULL COMMENT 'Session creation date',
  `closed` tinyint(1) NOT NULL default '0' COMMENT '1 - closed, 0 - opened',
  `data` text COMMENT 'Any additional serialized data',
  PRIMARY KEY  (`sid`)
);

CREATE TABLE `#PREF_lc_users` (
  `user_id` int(11) unsigned NOT NULL auto_increment,
  `sid` int(11) unsigned NOT NULL default '0',
  `agent_id` int(11) default NULL COMMENT 'Unique id of system agent',
  `user_ip` varchar(50) NOT NULL default '',
  `hostname` varchar(255) NULL,
  `nickname` varchar(55) NOT NULL default '',
  `actual_time` datetime default NULL COMMENT 'Any last user action time',
  `user_key` varchar(50) NOT NULL default '',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `write_activity` int(11) default NULL COMMENT 'Microseconds from last time of user typing',
  `status` varchar(25) NOT NULL default 'created' COMMENT 'LC user state status: created, chating, requesting, ...',
  `data` text NOT NULL COMMENT 'Any serialized additional user data',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `sid_agent` (`sid`,`agent_id`)
);

CREATE TABLE `#PREF_lc_users_messages_status` (
  `user_id` int(11) unsigned NOT NULL default '0',
  `message_id` int(11) NOT NULL default '0',
  `get_time` datetime default NULL,
  PRIMARY KEY  (`user_id`,`message_id`)
);
