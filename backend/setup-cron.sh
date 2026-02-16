#!/bin/bash

# Setup cron jobs for www-data user
echo "Setting up cron jobs for www-data user..."

# Ensure cron directories exist
mkdir -p /var/spool/cron/crontabs
mkdir -p /var/www/html/storage/cron

# Create cron jobs for www-data user
cat > /var/spool/cron/crontabs/www-data << EOF
* * * * * cd /var/www/html && php storage/cron/runner.php >> /var/log/cron.log 2>&1
* * * * * cd /var/www/html && bash storage/cron/runner.bash >> /var/log/cron.log 2>&1
EOF

# Set proper permissions
chmod 600 /var/spool/cron/crontabs/www-data
chown www-data:crontab /var/spool/cron/crontabs/www-data

# Create log file for cron
touch /var/log/cron.log
chown www-data:www-data /var/log/cron.log

# Ensure cron daemon is running
service cron start

echo "Cron jobs setup completed for www-data user"