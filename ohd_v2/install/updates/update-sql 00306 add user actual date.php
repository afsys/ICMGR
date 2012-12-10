ALTER TABLE `#_PREF_users`
  ADD COLUMN `last_action_time` datetime NULL COMMENT 'Determine last uses`s page request.' AFTER `disable_at`;