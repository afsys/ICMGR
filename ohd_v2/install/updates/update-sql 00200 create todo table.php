CREATE TABLE `#_PREF_todo_items` (
  `tdi_id` int(11) unsigned NOT NULL auto_increment,
  `ticket_id` int(11) default NULL,
  `assigned_to` int(11) default NULL,
  `caption` varchar(255) NOT NULL default '',
  `description` tinytext,
  `priority` varchar(55) default NULL,
  `status` varchar(55) default NULL,
  `time_to_make` varchar(200) default NULL,
  `progress` int(4) NOT NULL default '0',
  `eta` datetime default NULL,
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `updated_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `closed_at` datetime default NULL,
  PRIMARY KEY  (`tdi_id`)
) TYPE=MyISAM;

