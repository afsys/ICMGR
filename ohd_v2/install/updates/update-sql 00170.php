CREATE TABLE `#_PREF_users_tickets_props` (
  `user_id` int(11) unsigned NOT NULL default '0',
  `ticket_id` int(11) unsigned NOT NULL default '0',
  `last_view_time` datetime default NULL,
  PRIMARY KEY  (`user_id`,`ticket_id`)
)