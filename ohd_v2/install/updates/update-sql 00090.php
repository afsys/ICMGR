ALTER TABLE `announcements` RENAME TO `ohd_announcements`;
ALTER TABLE `canned_emails` RENAME TO `ohd_canned_emails`;
ALTER TABLE `canned_emails_categories` RENAME TO `ohd_canned_emails_categories`;
ALTER TABLE `emails_history` RENAME TO `ohd_emails_history`;
ALTER TABLE `groups` RENAME TO `ohd_groups`;
ALTER TABLE `kb_categories` RENAME TO `ohd_kb_categories`;
ALTER TABLE `kb_items` RENAME TO `ohd_kb_items`;
ALTER TABLE `kb_items_notes` RENAME TO `ohd_kb_items_notes`;
ALTER TABLE `kb_items_raiting` RENAME TO `ohd_kb_items_raiting`;


ALTER TABLE `ohd_config_string` RENAME TO `ohd_config_string`;
ALTER TABLE `ohd_email_filter` RENAME TO `ohd_email_filter`;
ALTER TABLE `ohd_email_handled_uid` RENAME TO `ohd_email_handled_uid`;
ALTER TABLE `ohd_emails_log` RENAME TO `ohd_emails_log`;
ALTER TABLE `sys_options` RENAME TO `ohd_sys_options`;
ALTER TABLE `tickets` RENAME TO `ohd_tickets`;
ALTER TABLE `tickets_history` RENAME TO `ohd_tickets_history`;


ALTER TABLE `tickets_messages` RENAME TO `ohd_tickets_messages`;
ALTER TABLE `tickets_products` RENAME TO `ohd_tickets_products`;
ALTER TABLE `tickets_products_form_fields` RENAME TO `ohd_tickets_products_form_fields`;
ALTER TABLE `tickets_products_forms_values` RENAME TO `ohd_tickets_products_forms_values`;
ALTER TABLE `tickets_time_tracking` RENAME TO `ohd_tickets_time_tracking`;

ALTER TABLE `users` RENAME TO `ohd_users`;
ALTER TABLE `users_groups` RENAME TO `ohd_users_groups`;
ALTER TABLE `users_options` RENAME TO `ohd_users_options`;