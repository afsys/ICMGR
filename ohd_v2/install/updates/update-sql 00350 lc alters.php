ALTER TABLE #_PREF_lc_sessions
  ADD COLUMN `other_close_reason` varchar(120) NULL AFTER `closed`;
  
ALTER TABLE #_PREF_lc_sessions
  ADD COLUMN `closed_at` datetime NULL AFTER `other_close_reason`;