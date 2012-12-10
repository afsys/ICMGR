ALTER TABLE #_PREF_tickets_messages
  ADD COLUMN `message_subject` varchar(255) NULL COMMENT 'Subject text from email on email piping' AFTER `message_type`;