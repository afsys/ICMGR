ALTER TABLE #_PREF_users
  CHANGE COLUMN `last_action_time` `actual_time` datetime NULL COMMENT 'Determine last users`s page request.';