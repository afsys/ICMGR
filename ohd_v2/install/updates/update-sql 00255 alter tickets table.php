ALTER TABLE #_PREF_tickets
  ADD COLUMN `expired_at` date NULL AFTER `created_at`;
  
ALTER TABLE #_PREF_tickets
  ADD COLUMN `hash_code` varchar(32) NULL;  
  
INSERT INTO `#_PREF_sys_options` VALUES ('emails_templates','ticket_closed_by_advisor',4,'THIS IS AN AUTO-GENERATED EMAIL FROM AUTOMATED HELP DESK...<br/>\r\n<br/>\r\nAdvisor have closed ticket related to yout request.<br/>\r\nYou can view the current status of your ticket using this URL: <a href=\"%7B$ticket_url%7D\">{$ticket_url}</a><br/>\r\n<p>Thank you for the opportunity to serve you!</p>',0);