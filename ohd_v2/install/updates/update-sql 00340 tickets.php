ALTER TABLE #_PREF_tickets_messages
  ADD UNIQUE INDEX `ticket_id_ind` (`message_id`),
  DROP INDEX `ticket_id_ind`;
  
ALTER TABLE #_PREF_tickets_messages
  CHANGE COLUMN `message_id` `message_id` int(6) NULL auto_increment;  
  
ALTER TABLE #_PREF_tickets_messages
  CHANGE COLUMN `ticket_id` `ticket_id` int(6) NOT NULL DEFAULT 0 AFTER `message_id`;  