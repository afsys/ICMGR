ALTER TABLE #_PREF_lc_sessions
  ADD COLUMN `trasfer_to_agent` int(11) NULL;
  
ALTER TABLE #_PREF_lc_sessions
  ADD COLUMN `transfer_agent_responce` varchar(30) NULL;