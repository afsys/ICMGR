ALTER TABLE #_PREF_lc_conversations
  CHANGE COLUMN `service_command_params` `message_params` text NULL COMMENT 'service commans parameters (serialized php array)' AFTER `message`;