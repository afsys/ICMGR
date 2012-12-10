CREATE TABLE `#_PREF_piping_emails_log` (
  `email_id` int(11) unsigned NOT NULL auto_increment,
  `email_uid` varchar(100) NOT NULL default '',
  `email_subject` varchar(255) NOT NULL default '',
  `email_headers` text NOT NULL,
  `email_body` text,
  `email_raw_message` text,
  `email_added_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `email_status` varchar(20) NOT NULL default 'ok',
  `added_to_ticket_id` int(11) NOT NULL default '0',
  `added_to_message_id` int(11) default NULL,
  PRIMARY KEY  (`email_id`)
) TYPE=MyISAM;

