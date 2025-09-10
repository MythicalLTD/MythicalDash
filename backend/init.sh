#!/bin/bash

# Ensure storage/.env matches container networking
ENV_FILE="/var/www/html/storage/.env"
mkdir -p /var/www/html/storage
if [ -f "$ENV_FILE" ]; then
	# Update existing keys or append if missing
	grep -q '^REDIS_HOST=' "$ENV_FILE" && sed -i 's/^REDIS_HOST=.*/REDIS_HOST=redis/' "$ENV_FILE" || echo 'REDIS_HOST=redis' >> "$ENV_FILE"
	[ -n "$REDIS_PASSWORD" ] && (grep -q '^REDIS_PASSWORD=' "$ENV_FILE" && sed -i "s/^REDIS_PASSWORD=.*/REDIS_PASSWORD=${REDIS_PASSWORD}/" "$ENV_FILE" || echo "REDIS_PASSWORD=${REDIS_PASSWORD}" >> "$ENV_FILE")
	grep -q '^DATABASE_HOST=' "$ENV_FILE" && sed -i 's/^DATABASE_HOST=.*/DATABASE_HOST=mysql/' "$ENV_FILE" || echo 'DATABASE_HOST=mysql' >> "$ENV_FILE"
	grep -q '^DATABASE_PORT=' "$ENV_FILE" && sed -i 's/^DATABASE_PORT=.*/DATABASE_PORT=3306/' "$ENV_FILE" || echo 'DATABASE_PORT=3306' >> "$ENV_FILE"
	[ -n "$DATABASE_DATABASE" ] && (grep -q '^DATABASE_DATABASE=' "$ENV_FILE" && sed -i "s/^DATABASE_DATABASE=.*/DATABASE_DATABASE=${DATABASE_DATABASE}/" "$ENV_FILE" || echo "DATABASE_DATABASE=${DATABASE_DATABASE}" >> "$ENV_FILE")
	[ -n "$DATABASE_USER" ] && (grep -q '^DATABASE_USER=' "$ENV_FILE" && sed -i "s/^DATABASE_USER=.*/DATABASE_USER=${DATABASE_USER}/" "$ENV_FILE" || echo "DATABASE_USER=${DATABASE_USER}" >> "$ENV_FILE")
	[ -n "$DATABASE_PASSWORD" ] && (grep -q '^DATABASE_PASSWORD=' "$ENV_FILE" && sed -i "s/^DATABASE_PASSWORD=.*/DATABASE_PASSWORD=${DATABASE_PASSWORD}/" "$ENV_FILE" || echo "DATABASE_PASSWORD=${DATABASE_PASSWORD}" >> "$ENV_FILE")
else
	cat > "$ENV_FILE" <<EOF
DATABASE_HOST=mysql
DATABASE_PORT=3306
DATABASE_DATABASE=${DATABASE_DATABASE:-mythicaldash_v3}
DATABASE_USER=${DATABASE_USER:-mythicaldash_v3}
DATABASE_PASSWORD=${DATABASE_PASSWORD:-mythicaldash_v3_password}
DATABASE_ENCRYPTION=plaintext
REDIS_HOST=redis
REDIS_PASSWORD=${REDIS_PASSWORD:-mythicaldash_v3_redis}
EOF
fi

# Brief TCP wait (no auth)
echo "Waiting for MariaDB (tcp://mysql:3306) to be reachable..."
max_attempts=10
attempt=0
while [ $attempt -lt $max_attempts ]; do
	if bash -c "</dev/tcp/mysql/3306" 2>/dev/null; then
		echo "MariaDB TCP is reachable!"
		break
	fi
	attempt=$((attempt + 1))
	echo "MariaDB not reachable yet. Waiting... (attempt $attempt/$max_attempts)"
	sleep 3
done

# Make sure composer packages are installed
echo "Installing composer packages..."
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader
composer_exit_code=$?
if [ $composer_exit_code -ne 0 ]; then
    echo "Composer packages failed with exit code $composer_exit_code"
    exit $composer_exit_code
fi
echo "Composer packages installed."

# Run migrations
echo "Running migrations..."
php /var/www/html/cli migrate
migration_exit_code=$?
if [ $migration_exit_code -ne 0 ]; then
    echo "Migrations failed with exit code $migration_exit_code"
    exit $migration_exit_code
fi
echo "Migrations finished."

# Set everything to be owned by www-data with 777 permissions
echo "Setting ownership and permissions..."
chown -R www-data:www-data /var/www/html
chmod -R 777 /var/www/html
echo "Ownership and permissions set."

# Setup cron jobs (fallback method)
echo "Setting up cron jobs..."
/usr/local/bin/setup-cron.sh
echo "Cron jobs setup completed."

# Note: The main cron execution will be handled by supervisord using cron-runner.sh

# Start the main application
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf