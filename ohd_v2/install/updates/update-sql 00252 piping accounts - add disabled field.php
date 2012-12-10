ALTER TABLE #_PREF_piping_accounts
  ADD COLUMN `acc_disabled` tinyint(1) NOT NULL DEFAULT 0;
  
ALTER TABLE #_PREF_tickets
  ADD COLUMN `customer_add_emails` text NULL AFTER `customer_email`;  