#!/bin/bash

# Cron runner script that executes tasks every minute
# This is more reliable than cron in Docker containers

echo "$(date): Starting cron runner for www-data user..."

# Ensure we have sudo access
if ! sudo -n true 2>/dev/null; then
    echo "$(date): Warning: sudo access may not be available, trying alternative approach..."
fi

# Function to run PHP cron job
run_php_cron() {
    echo "$(date): Running PHP cron job..."
    cd /var/www/html
    
    # Try with sudo first, fallback to direct execution
    if sudo -u www-data php storage/cron/runner.php >> /var/log/cron.log 2>&1; then
        echo "$(date): PHP cron job completed with sudo"
    else
        echo "$(date): Trying PHP cron job without sudo..."
        php storage/cron/runner.php >> /var/log/cron.log 2>&1
        echo "$(date): PHP cron job completed without sudo"
    fi
}

# Function to run bash cron job
run_bash_cron() {
    echo "$(date): Running bash cron job..."
    cd /var/www/html
    
    # Try with sudo first, fallback to direct execution
    if sudo -u www-data bash storage/cron/runner.bash >> /var/log/cron.log 2>&1; then
        echo "$(date): Bash cron job completed with sudo"
    else
        echo "$(date): Trying bash cron job without sudo..."
        bash storage/cron/runner.bash >> /var/log/cron.log 2>&1
        echo "$(date): Bash cron job completed without sudo"
    fi
}

# Create log file if it doesn't exist
touch /var/log/cron.log
chown www-data:www-data /var/log/cron.log

# Main loop - run every minute
while true; do
    echo "$(date): Starting cron cycle..."
    
    # Run both cron jobs
    run_php_cron
    run_bash_cron
    
    echo "$(date): Cron cycle completed, waiting 60 seconds..."
    sleep 60
done
