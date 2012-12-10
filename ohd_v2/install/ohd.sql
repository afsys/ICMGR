                                      

CREATE TABLE `#PREF_announcements` (
  `ann_id` int(6) unsigned NOT NULL auto_increment,
  `ann_caption` varchar(30) default NULL,
  `ann_short` varchar(100) default NULL,
  `ann_full` text,
  `ann_date` datetime default NULL,
  PRIMARY KEY  (`ann_id`)
) TYPE=MyISAM;






CREATE TABLE `#PREF_canned_emails` (
  `email_id` int(11) NOT NULL auto_increment,
  `cat_id` int(11) default '0',
  `email_caption` varchar(255) NOT NULL default '',
  `email_content` text,
  `highlighted` tinyint(1) NOT NULL DEFAULT 0,  
  PRIMARY KEY  (`email_id`)
) TYPE=MyISAM;


INSERT INTO `#PREF_canned_emails` VALUES (1,1,'Canned Email Example Template','Hi, %USER_NAME%!\r\n\r\nYou could use predefined variables like: %CURR_DATE%, \r\nor write any common text.\r\n',0);








CREATE TABLE `#PREF_canned_emails_categories` (
  `cat_id` int(6) unsigned NOT NULL auto_increment,
  `cat_caption` varchar(55) default NULL,
  `cat_desc` tinytext,
  PRIMARY KEY  (`cat_id`)
) TYPE=MyISAM;


INSERT INTO `#PREF_canned_emails_categories` VALUES (1,'Emails Group','You can add any group for emails');






CREATE TABLE `#PREF_emails_history` (
  `ticket_id` int(11) NOT NULL default '0',
  `email_id` int(6) unsigned NOT NULL default '0',
  `his_rec_id` int(11) default '0',
  `posted_by` int(11) NOT NULL default '0',
  `email` varchar(255) default NULL,
  `subj` varchar(255) default NULL,
  `message` text NOT NULL,
  `rec_date` datetime default NULL,
  `is_canned_email` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`ticket_id`,`email_id`)
) TYPE=MyISAM;





CREATE TABLE `#PREF_emails_log` (
  `id` int(11) NOT NULL auto_increment,
  `mail_from_name` varchar(255) default NULL,
  `mail_to_name` varchar(255) default NULL,
  `mail_from_email` varchar(255) default NULL,
  `mail_to_email` varchar(255) default NULL,
  `mail_subject` varchar(255) default NULL,
  `mail_send_time` datetime default '0000-00-00 00:00:00',
  `mail_send_result` varchar(255) default NULL,
  `mail_debug_msg` text,
  `backtrace` text NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;







CREATE TABLE `#PREF_groups` (
  `group_id` int(6) NOT NULL default '0',
  `group_caption` varchar(25) default NULL,
  `group_comment` varchar(255) default NULL,
  PRIMARY KEY  (`group_id`),
  UNIQUE KEY `CaptionIndex` (`group_caption`)
) TYPE=MyISAM;


INSERT INTO `#PREF_groups` VALUES (3,'Support','Support Team');
INSERT INTO `#PREF_groups` VALUES (2,'Admin','Help Desk Administrative Group');
INSERT INTO `#PREF_groups` VALUES (4,'Sales','Sales Team');






CREATE TABLE `#PREF_config_string` (
  `name` varchar(100) NOT NULL default '',
  `value` text NOT NULL,
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM;


INSERT INTO `#PREF_config_string` VALUES ('email_piping_port','110');
INSERT INTO `#PREF_config_string` VALUES ('email_piping_login','login');
INSERT INTO `#PREF_config_string` VALUES ('email_piping_server','localhost');
INSERT INTO `#PREF_config_string` VALUES ('email_piping_password','pass');






CREATE TABLE `#PREF_email_filter` (
  `id` int(11) NOT NULL auto_increment,
  `addr_from` text NOT NULL,
  `addr_to` text NOT NULL,
  `subject` text NOT NULL,
  `words` text NOT NULL,
  `no_words` text NOT NULL,
  `ticket_group_id` int(11) default NULL,
  `ticket_product_id` int(11) NOT NULL default '0',
  `status` varchar(55) default NULL,
  `priority` varchar(55) default NULL,
  `assigned_to` int(11) default NULL,
  `add_email_as` varchar(55) NULL,
  `filter_order` int(4) NOT NULL default '0',  
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;





CREATE TABLE `#PREF_email_handled_uid` (
  `uid` char(132) NOT NULL default '',
  `ts_handled` timestamp NOT NULL,
  UNIQUE KEY `uid` (`uid`)
) TYPE=MyISAM;




CREATE TABLE `#PREF_piping_accounts` (
  `acc_id` int(11) unsigned NOT NULL auto_increment,
  `acc_caption` varchar(55) default NULL,
  `acc_host` varchar(55) default NULL,
  `acc_port` int(4) default NULL,
  `acc_login` varchar(55) default NULL,
  `acc_pass` varchar(55) default NULL,
  `acc_disabled` tinyint(1) NOT NULL DEFAULT 0,
  `delete_email` int(1) NOT NULL DEFAULT 0,  
  PRIMARY KEY  (`acc_id`)
) TYPE=MyISAM;


CREATE TABLE `#PREF_piping_emails_log` (
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




CREATE TABLE `#PREF_sys_options` (
  `option_group` varchar(50) NOT NULL default '0',
  `option_name` varchar(50) NOT NULL default '',
  `option_index` int(4) NOT NULL default '0',
  `option_value` text,
  `is_serialized` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`option_group`,`option_name`)
) TYPE=MyISAM;


INSERT INTO `#PREF_sys_options` VALUES ('attachments','allow',1,'0',0);
INSERT INTO `#PREF_sys_options` VALUES ('script_urls','new_ticket',1,'http://www.yourdomain.com/ohd/forms/addTicket.php',0);
INSERT INTO `#PREF_sys_options` VALUES ('script_urls','existing_ticket',2,'http://www.yourdomain.com/ohd/forms/showTickets.php',0);

INSERT INTO `#PREF_sys_options` VALUES ('defaults','ticket_product_id',1,'5',0);

INSERT INTO `#PREF_sys_options` VALUES ('notification_emails','defect_added',1,'1',0);
INSERT INTO `#PREF_sys_options` VALUES ('notification_emails','defect_closed',2,'1',0);

INSERT INTO `#PREF_sys_options` VALUES ('common','ticket_form_url',1,'http://www.yourdomain.com/ohd/forms/showTickets.php',0);
INSERT INTO `#PREF_sys_options` VALUES ('common','company_name',2,'Company Name',0);
INSERT INTO `#PREF_sys_options` VALUES ('common','admin_email',3,'admin@email',0);

INSERT INTO `#PREF_sys_options` VALUES ('tickets','status_for_new',1,'New',0);
INSERT INTO `#PREF_sys_options` VALUES ('tickets','status_for_closed',2,'Closed',0);
INSERT INTO `#PREF_sys_options` VALUES ('tickets','status_for_reopened',3,'Open',0);

INSERT INTO `#PREF_sys_options` VALUES ('tickets_list','show_product_name',1,'1',0);
INSERT INTO `#PREF_sys_options` VALUES ('tickets_list','flood_protection',2,'0',0);
INSERT INTO `#PREF_sys_options` VALUES ('tickets_list','purge_opened',3,'7',0);
INSERT INTO `#PREF_sys_options` VALUES ('tickets_list','banned_emails',4,'',0);
INSERT INTO `#PREF_sys_options` VALUES ('tickets_list','banned_ips',5,'',0);

INSERT INTO `#PREF_sys_options` VALUES ('ticket_statuses','New',1,'a:3:{s:9:\"textcolor\";s:7:\"#000000\";s:11:\"bordercolor\";s:7:\"#000000\";s:7:\"bgcolor\";s:7:\"#999999\";}',1);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_statuses','Open',2,'a:3:{s:9:\"textcolor\";s:0:\"\";s:11:\"bordercolor\";s:0:\"\";s:7:\"bgcolor\";s:0:\"\";}',1);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_statuses','Closed',3,'a:3:{s:9:\"textcolor\";s:0:\"\";s:11:\"bordercolor\";s:0:\"\";s:7:\"bgcolor\";s:0:\"\";}',1);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_statuses','Pending',4,'a:3:{s:9:\"textcolor\";s:7:\"#660000\";s:11:\"bordercolor\";s:7:\"#000000\";s:7:\"bgcolor\";s:7:\"#FFFFCC\";}',1);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_statuses','Ready',5,'a:3:{s:9:\"textcolor\";s:7:\"#0000FF\";s:11:\"bordercolor\";s:7:\"#000000\";s:7:\"bgcolor\";s:7:\"#9999FF\";}',1);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_statuses','Deferred',6,'a:3:{s:9:\"textcolor\";s:7:\"#000033\";s:11:\"bordercolor\";s:7:\"#000000\";s:7:\"bgcolor\";s:7:\"#00CC00\";}',1);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_statuses','Fraud',7,'a:3:{s:9:\"textcolor\";s:7:\"#000000\";s:11:\"bordercolor\";s:7:\"#000000\";s:7:\"bgcolor\";s:7:\"#FF3333\";}',1);

INSERT INTO `#PREF_sys_options` VALUES ('ticket_priorities','Urgent',1,'a:3:{s:9:\"textcolor\";s:7:\"#CC0000\";s:11:\"bordercolor\";s:7:\"#000000\";s:7:\"bgcolor\";s:7:\"#FFFF00\";}',1);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_priorities','High',2,'a:3:{s:9:\"textcolor\";s:7:\"#CC0000\";s:11:\"bordercolor\";s:7:\"#FF0000\";s:7:\"bgcolor\";s:7:\"#FFFFFF\";}',1);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_priorities','Normal',3,'a:3:{s:9:\"textcolor\";s:0:\"\";s:11:\"bordercolor\";s:0:\"\";s:7:\"bgcolor\";s:0:\"\";}',1);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_priorities','Low',4,'a:3:{s:9:\"textcolor\";s:0:\"\";s:11:\"bordercolor\";s:0:\"\";s:7:\"bgcolor\";s:0:\"\";}',1);

INSERT INTO `#PREF_sys_options` VALUES ('ticket_types','Query',1,NULL,0);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_types','Install Problem',2,NULL,0);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_types','Custom Scripting',3,NULL,0);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_types','Estimate',4,NULL,0);
INSERT INTO `#PREF_sys_options` VALUES ('ticket_types','Recurring Pymt Request',5,NULL,0);

INSERT INTO `#PREF_sys_options` VALUES ('emails_templates','ticket_created',1,'THIS IS AN AUTO-GENERATED EMAIL FROM AUTOMATED HELP DESK...\r\n<p>Thank you for submitting your request.&nbsp; Your request is important to us.&nbsp; In an effort to provide outstanding customer service, you will be provided with emailed updates as we process your request.&nbsp; You may view the status of your request using link below.&nbsp; You can review the status of your request at any time.&nbsp; If you have additional questions or information, simply click on the link below and post a message to your service advisor.</p>\r\n<p>You can view the current status of your ticket using this URL: <a href=\"{$ticket_url}\">{$ticket_url}</a></p>\r\n<p>All of us look forward to serving you!</p>',0);
INSERT INTO `#PREF_sys_options` VALUES ('emails_templates','ticket_created_by_advisor',2,'THIS IS AN AUTO-GENERATED EMAIL FROM AUTOMATED HELP DESK...<br/>\r\n<br/>\r\nAdvisor have created ticket related to your request.<br/>\r\nYou can view the current status of your ticket using this URL: <a href=\"{$ticket_url}\">{$ticket_url}</a>\r\n<p>Thank you for the opportunity to serve you!</p>',0);
INSERT INTO `#PREF_sys_options` VALUES ('emails_templates','ticket_new_message',3,'THIS IS AN AUTO-GENERATED EMAIL FROM AUTOMATED HELP DESK...\r\n<p>Your service request has been updated by your service advisor.&nbsp; You may view the updated status by clicking on the link below.&nbsp; If you have additional questions or information, simply click on the link below and post a message to your service advisor.<br/>\r\n<br/>\r\nYou can view the current status of your ticket using this URL: <a href=\"{$ticket_url}\">{$ticket_url}</a></p>\r\nMessage:<br/>\r\n{$ticket_message} <hr size=\"1\" noshade=\"noshade\"/>\r\n<p>Thank you for the opportunity to serve you! </p>',0);
INSERT INTO `#PREF_sys_options` VALUES ('emails_templates','ticket_closed_by_advisor',4,'THIS IS AN AUTO-GENERATED EMAIL FROM AUTOMATED HELP DESK...<br/>\r\n<br/>\r\nAdvisor have closed ticket related to yout request.<br/>\r\nYou can view the current status of your ticket using this URL: <a href=\"{$ticket_url}\">{$ticket_url}</a><br/>\r\n<p>Thank you for the opportunity to serve you!</p>',0);
INSERT INTO `#PREF_sys_options` VALUES ('emails_templates','ticket_renamed_by_advisor',5,'THIS IS AN AUTO-GENERATED EMAIL FROM AUTOMATED HELP DESK...<br/>\r\n<br/>\r\nAdvisor have renamed ticket related to yout request.<br/>\r\nYou can view the current status of your ticket using this <span style=\"font-weight: bold;\">NEW </span>URL: <a href=\"{$ticket_url}\">{$ticket_url}</a><br/>\r\n<p>Thank you for the opportunity to serve you!</p>',0);

INSERT INTO `#PREF_sys_options` VALUES ('emails_templates','ticket_closed_by_advisor_subject',  6,' has been closed',0);
INSERT INTO `#PREF_sys_options` VALUES ('emails_templates','ticket_created_subject',            7,' has been created',0);
INSERT INTO `#PREF_sys_options` VALUES ('emails_templates','ticket_new_message_subject',        8,': New message posted',0);
INSERT INTO `#PREF_sys_options` VALUES ('emails_templates','ticket_renamed_by_advisor_subject', 9,' is being reviewed',0);



CREATE TABLE `#PREF_tickets` (
  `ticket_id` int(6) NOT NULL auto_increment,
  `ticket_num` varchar(20) NOT NULL default '0',
  `group_id` int(11) NOT NULL default '0',
  `ticket_product_id` int(6) NOT NULL default '0',
  `ticket_product_ver` varchar(50) default NULL,
  `creator_user_id` int(4) NOT NULL default '0',
  `creator_user_name` varchar(100) default NULL,
  `creator_user_ip` varchar(20) default NULL,
  `caption` varchar(255) default NULL,
  `description` text,
  `description_is_html` tinyint(1) NOT NULL default '0',
  `customer_name` varchar(255) default NULL,
  `customer_email` varchar(255) default NULL,
  `customer_add_emails` text NULL,
  `customer_phone` varchar(55) NOT NULL default '',
  `customer_last_open_date` datetime NULL,
  `carbon_copy_email` text NULL,
  `status` varchar(55) NOT NULL default '',
  `priority` varchar(55) NOT NULL default '',
  `type` varchar(50) NOT NULL default '',
  `created_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `expired_at` date, 
  `modified_at` datetime default NULL,
  `closed_at` datetime default NULL,
  `assigned_to` int(11) NOT NULL default '0',
  `ticket_email_customer` tinyint(1) NOT NULL default '0',
  `is_in_trash_folder` tinyint(1) NOT NULL default '0',
  `last_message_posted_by_admin` tinyint(1) NOT NULL default 0,
  `ex_opt_exclude_from_common_list` tinyint(1) NOT NULL DEFAULT 0,
  `due_date` date NULL,  
  `hash_code` varchar(32) NULL,
  `add_options` text NULL,
  `flags` varchar(255) NULL,
  PRIMARY KEY  (`ticket_id`),
  UNIQUE INDEX `ticket_num` (`ticket_num`(20))
) TYPE=MyISAM;






CREATE TABLE `#PREF_tickets_emails_query` (
  `email_id` int(11) unsigned NOT NULL auto_increment,
  `email_to` varchar(255) NOT NULL default '0',
  `send_to` varchar(255) NOT NULL default '',
  `subject` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  `send_from` text,
  `is_sended` tinyint(1) NOT NULL default '0',
  `sent_at` datetime default NULL,
  `allow_grouping` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`email_id`)
) TYPE=MyISAM;






CREATE TABLE `#PREF_tickets_history` (
  `his_rec_id` int(11) NOT NULL auto_increment,
  `ticket_id` int(6) unsigned NOT NULL default '0',
  `user_id` int(11) default NULL,
  `his_notes` varchar(255) default NULL,
  `rec_date` datetime default NULL,
  PRIMARY KEY  (`his_rec_id`)
) TYPE=MyISAM;





CREATE TABLE `#PREF_tickets_messages` (
  `ticket_id` int(6) NOT NULL default '0',
  `message_id` int(6) NOT NULL default '0',
  `message_creator_user_id` int(6) default '0',
  `message_creator_user_name` varchar(100) NOT NULL default '',
  `message_creator_user_ip` varchar(20) default NULL,
  `message_type` varchar(20) NOT NULL default '',
  `message_subject` varchar(255) NULL COMMENT 'Subject text from email on email piping',
  `message_text` text,
  `message_text_is_html` tinyint(1) NOT NULL default '0',
  `message_atachment_file` varchar(255) default NULL,
  `message_atachment_name` varchar(255) default NULL,
  `message_datetime` datetime default NULL,
  KEY `ticket_id_ind` (`ticket_id`)
) TYPE=MyISAM;

ALTER TABLE `#PREF_tickets_messages` ADD INDEX ( `message_creator_user_id` ); 
ALTER TABLE `#PREF_tickets_messages` ADD INDEX ( `message_datetime` ); 







CREATE TABLE `#PREF_tickets_products` (
  `ticket_product_id` int(6) NOT NULL default '0',
  `ticket_product_caption` varchar(255) default NULL,
  `ticket_product_desc` varchar(255) default NULL,
  `ticket_product_redirect_url` varchar(255) NOT NULL default '',
  `ticket_email_customer` tinyint(1) NOT NULL default '0',
  `ticket_product_email_customer` tinyint(1) NOT NULL default '0',
  `ticket_product_ver_enabled` tinyint(1) NOT NULL default '0',
  `ticket_product_ver_list` text,
  `default_tech` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`ticket_product_id`)
) TYPE=MyISAM;

INSERT INTO `#PREF_tickets_products` 
  VALUES (1,'Product','','',0,0,1,'Pro\r\nDeluxe\r\nDeluxe Plus', 0);





CREATE TABLE `#PREF_tickets_products_form_fields` (
  `ticket_product_id` int(6) NOT NULL default '0',
  `ticket_field_id` int(6) NOT NULL default '0',
  `ticket_filed_pos` int(11) default '0',
  `ticket_field_caption` varchar(55) default NULL,
  `ticket_field_type` varchar(50) default NULL,
  `ticket_field_options` text,
  `ticket_field_is_optional` tinyint(4) NOT NULL default '0',
  `show_in_userside` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`ticket_product_id`,`ticket_field_id`)
) TYPE=MyISAM;





CREATE TABLE `#PREF_tickets_products_forms_values` (
  `ticket_id` int(6) NOT NULL default '0',
  `ticket_product_id` int(6) NOT NULL default '0',
  `ticket_field_id` int(6) NOT NULL default '0',
  `ticket_field_value` varchar(55) default NULL,
  PRIMARY KEY  (`ticket_id`,`ticket_product_id`,`ticket_field_id`)
) TYPE=MyISAM;





CREATE TABLE `#PREF_tickets_time_tracking` (
  `tt_id` int(11) unsigned NOT NULL auto_increment,
  `ticket_id` int(11) NOT NULL default '0',
  `ticket_message_id` int(11) default NULL,
  `tracked_by_user_id` tinyint(4) NOT NULL default '0',
  `tt_worked` double(6,2) NOT NULL default '0.00',
  `tt_charged` double(6,2) NOT NULL default '0.00',
  `tt_billed` double(6,2) NOT NULL default '0.00',
  `tt_payed` double(6,2) NOT NULL default '0.00',
  `tt_notes` text,
  `tt_created` datetime default NULL,
  PRIMARY KEY  (`tt_id`)
) TYPE=MyISAM ;



CREATE TABLE `#PREF_todo_items` (
  `tdi_id` int(11) unsigned NOT NULL auto_increment,
  `ticket_id` int(11) default NULL,
  `assigned_to` int(11) default NULL,
  `caption` varchar(255) NOT NULL default '',
  `description` text,
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




CREATE TABLE `#PREF_updates` (
  `rev_num` int(11) unsigned NOT NULL default '0',
  `type` varchar(10) NOT NULL default '',
  `status` varchar(20) default NULL,
  PRIMARY KEY  (`rev_num`,`type`)
) TYPE=MyISAM;








CREATE TABLE `#PREF_users` (
  `user_id` int(6) NOT NULL default '0',
  `user_login` varchar(50) default NULL,
  `user_pass` varchar(50) default NULL,
  `user_name` varchar(50) default NULL,
  `user_lastname` varchar(50) default NULL,
  `user_email` varchar(50) default NULL,
  `user_enabled` tinyint(1) default NULL,
  `user_rights` int(11) default NULL,
  `is_customer` tinyint(1) NOT NULL default '0',
  `is_sys_admin` tinyint(1) NOT NULL default '0',
  `last_message_time` datetime default NULL,
  `register_code` varchar(60) NULL,  
  `disable_at` date NULL,  
  `actual_time` datetime NULL COMMENT 'Last users action time',
  `lc_enabled` tinyint(4) NOT NULL default '1',
  `lc_user_request` int(11) default NULL,
  `lc_priority` int(4) NOT NULL default '0',
  PRIMARY KEY  (`user_id`)
) TYPE=MyISAM;

INSERT INTO `#PREF_users` VALUES (1,'admin','admin','System','Administrator','slyder@homa.net.ua',NULL,0,0,1,NULL,NULL,NULL,NULL,0,NULL,0);





CREATE TABLE `#PREF_users_groups` (
  `user_id` int(6) NOT NULL default '0',
  `group_id` int(6) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`group_id`)
) TYPE=MyISAM;






CREATE TABLE `#PREF_users_options` (
  `user_id` int(11) NOT NULL default '0',
  `option_group` varchar(50) NOT NULL default '0',
  `option_name` varchar(50) NOT NULL default '',
  `option_index` int(4) NOT NULL default '0',
  `option_value` varchar(50) default NULL,
  `is_serialized` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`user_id`,`option_group`,`option_name`)
) TYPE=MyISAM;







CREATE TABLE `#PREF_users_tickets_props` (
  `user_id` int(11) unsigned NOT NULL default '0',
  `ticket_id` int(11) unsigned NOT NULL default '0',
  `last_view_time` datetime default NULL,
  PRIMARY KEY  (`user_id`,`ticket_id`)
) TYPE=MyISAM ;
