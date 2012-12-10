ALTER TABLE #_PREF_lc_users
  ADD COLUMN `req_group_id` int(11) NULL;
  
ALTER TABLE #_PREF_lc_users
  ADD COLUMN `created_at` datetime NULL;