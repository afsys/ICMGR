CREATE TABLE `#_PREF_updates` (
  `rev_num` int(11) unsigned NOT NULL default '0',
  `type` varchar(10) NOT NULL default '',
  `status` varchar(20) default NULL,
  PRIMARY KEY  (`rev_num`,`type`)
);
