ALTER TABLE #_PREF_lc_conversations
  ADD COLUMN `service_command_params` text NULL 
      COMMENT 'service commans parameters (serialized php array)' AFTER `service_command`;