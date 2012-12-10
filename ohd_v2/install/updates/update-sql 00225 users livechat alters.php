ALTER TABLE #_PREF_users
  ADD COLUMN lc_enabled tinyint(4) NOT NULL default '1';

ALTER TABLE #_PREF_users
  ADD COLUMN lc_user_request int(11) default NULL;

ALTER TABLE #_PREF_users
  ADD COLUMN lc_user_request_time datetime default NULL;

ALTER TABLE #_PREF_users
  ADD COLUMN lc_priority int(4) NOT NULL default '0'