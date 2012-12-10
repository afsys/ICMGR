ALTER TABLE #_PREF_tickets
  ADD `ex_opt_exclude_from_common_list` TINYINT( 1 ) DEFAULT '0' NOT NULL ,
  ADD `due_date` DATETIME;

ALTER TABLE #_PREF_tickets_products ADD `default_tech` INT( 11 ) DEFAULT '0' NOT NULL ;