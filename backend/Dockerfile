# Use PHP 8.4 with FPM
FROM php:8.4-fpm

# Set working directory
WORKDIR /var/www/html

# Set environment variables to avoid interactive prompts
ENV DEBIAN_FRONTEND=noninteractive
ENV TERM=xterm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libgd-dev \
    libicu-dev \
    libpq-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libssl-dev \
    nginx \
    supervisor \
    mariadb-client \
	cron \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# Install PHP extensions (based on health check requirements)
# Note: pdo, json, openssl, curl, fileinfo, xml, dom, sodium, exif, pcntl, opcache are built-in
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        intl \
        bcmath

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Reset environment variables
ENV DEBIAN_FRONTEND=

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files with correct ownership
COPY --chown=www-data:www-data . .

# Install PHP dependencies
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

# Configure PHP (based on health check recommendations)
RUN echo 'memory_limit = -1\n\
upload_max_filesize = 100M\n\
post_max_size = 100M\n\
max_execution_time = 300\n\
max_input_vars = 3000\n\
max_file_uploads = 20\n\
display_errors = Off\n\
expose_php = Off\n\
log_errors = On\n\
error_log = /var/log/php_errors.log\n\
allow_url_fopen = On\n\
date.timezone = UTC\n\
opcache.enable = 1\n\
opcache.memory_consumption = 128\n\
opcache.interned_strings_buffer = 8\n\
opcache.max_accelerated_files = 4000\n\
opcache.revalidate_freq = 2\n\
opcache.fast_shutdown = 1' > /usr/local/etc/php/conf.d/custom.ini

# Configure nginx
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Configure supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create necessary directories
RUN mkdir -p /var/log/supervisor \
    && mkdir -p /run/php \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/cache \
    && mkdir -p /var/www/html/storage/uploads \
    && mkdir -p /var/www/html/storage/temp

# Copy and make init script executable
COPY init.sh /usr/local/bin/init.sh
RUN chmod +x /usr/local/bin/init.sh

 
# Create cron directory
RUN mkdir -p /var/spool/cron/crontabs \
    && mkdir -p /var/www/html/storage/cron

# Copy and make cron setup script executable
COPY setup-cron.sh /usr/local/bin/setup-cron.sh
RUN chmod +x /usr/local/bin/setup-cron.sh

# Copy and make cron runner script executable
COPY cron-runner.sh /usr/local/bin/cron-runner.sh
RUN chmod +x /usr/local/bin/cron-runner.sh

# Expose port 80
EXPOSE 80

# Start with init script
CMD ["/usr/local/bin/init.sh"]