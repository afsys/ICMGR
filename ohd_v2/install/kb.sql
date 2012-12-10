CREATE TABLE `#PREF_kb_categories` (
  `cat_id` int(11) NOT NULL auto_increment,
  `cat_parent_id` int(11) NOT NULL default '0',
  `cat_caption` varchar(100) NOT NULL default '',
  `cat_notes` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`cat_id`)
) TYPE=MyISAM;

INSERT INTO `#PREF_kb_categories` VALUES (1,0,'Test category','Some note for category...');






CREATE TABLE `#PREF_kb_items` (
  `item_id` int(11) NOT NULL default '0',
  `cat_id` int(11) NOT NULL default '0',
  `item_caption` varchar(255) default NULL,
  `item_notes` text NOT NULL,
  `item_viewed` int(11) NOT NULL default '0',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `expiration_date` date NULL,
  PRIMARY KEY  (`item_id`)
) TYPE=MyISAM;

INSERT INTO `#PREF_kb_items` VALUES (1,0,'KB Item 1','Some notes about KB Item 1',0,'2006-02-06 21:58:38', NULL);
INSERT INTO `#PREF_kb_items` VALUES (2,1,'KB Item 2','Some notes about KB Item 2',0,'2006-02-06 21:58:54', NULL);






CREATE TABLE `#PREF_kb_items_notes` (
  `item_id` int(11) NOT NULL default '0',
  `note_id` int(11) NOT NULL default '0',
  `note_user` varchar(50) NOT NULL default '',
  `note_text` varchar(255) default NULL,
  `note_date` datetime default NULL,
  `note_approved` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`item_id`,`note_id`)
) TYPE=MyISAM;






CREATE TABLE `#PREF_kb_items_raiting` (
  `item_id` int(11) default NULL,
  `rait_id` int(11) default NULL,
  `rait_value` int(11) default NULL,
  `rait_user_ip` varchar(25) default NULL,
  `rait_note_date` datetime default NULL
) TYPE=MyISAM;






