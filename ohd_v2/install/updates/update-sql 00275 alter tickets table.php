ALTER TABLE #_PREF_tickets
  CHANGE COLUMN `ticket_email_customer` `ticket_email_customer` int(4) unsigned NOT NULL DEFAULT 0;
  
ALTER TABLE #_PREF_tickets
  ADD COLUMN `add_options` text NULL;