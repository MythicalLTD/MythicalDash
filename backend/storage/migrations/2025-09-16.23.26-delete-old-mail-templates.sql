-- Templates currently in use: verify, reset_password, new_login, server_renewal_reminder, server_suspended, server_deleted
DELETE FROM `mythicaldash_mail_templates` WHERE `name` IN (
  'new_invoice',
  'product_ready',
  'product_suspended',
  'invoice_paid',
  'welcome'
);