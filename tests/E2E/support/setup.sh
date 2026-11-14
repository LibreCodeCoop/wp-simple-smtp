#!/bin/sh
set -e

wp core install --url=http://localhost:8889 --title=E2E --admin_user=admin --admin_password=password --admin_email=admin@example.org --skip-email
wp plugin activate wp-simple-smtp

wp option update smtp_host mailpit
wp option update smtp_port 1025
wp option update smtp_auth 1
wp option update smtp_user mailer
wp option update smtp_pass "pa'ss"
wp option update smtp_secure ''
wp option update smtp_from noreply@example.org
wp option update smtp_name 'Simple SMTP'
