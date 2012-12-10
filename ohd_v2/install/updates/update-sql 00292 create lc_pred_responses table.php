CREATE TABLE #_PREF_lc_pred_responses` 
  `resp_id` int(11) NOT NULL auto_increment,
  `resp_caption` varchar(255) default '',
  `resp_body` text,
  PRIMARY KEY  (`resp_id`)
)