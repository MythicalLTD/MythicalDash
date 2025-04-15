FROM php:8.3-fpm

WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    libzip-dev \
    libonig-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    git \
    cron \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        pdo \
        pdo_mysql \
        mysqli \
        bcmath \
        xml \
        mbstring \
        curl \
        zip

# Install Redis extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create required directories
RUN mkdir -p /var/www/html/public/attachments \
    && mkdir -p /var/www/html/storage/caches \
    && mkdir -p /var/www/html/storage/logs

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install dependencies
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader

# Copy rest of the application
COPY . .

# Add a cron job
RUN (crontab -l 2>/dev/null || echo "") | { cat; echo "* * * * * cd /var/www/html && php mythicaldash schedule_work >> /dev/null 2>&1"; } | crontab -
RUN (crontab -l 2>/dev/null || echo "") | { cat; echo "* * * * * cd /var/www/html/storage/cron && php runner.php >> /dev/null 2>&1"; } | crontab -
RUN (crontab -l 2>/dev/null || echo "") | { cat; echo "* * * * * cd /var/www/html/storage/cron && bash runner.php >> /dev/null 2>&1"; } | crontab -

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html \
    && find /var/www/html/storage -type d -exec chmod 775 {} \; \
    && find /var/www/html/storage -type f -exec chmod 664 {} \; \
    && find /var/www/html/public/attachments -type d -exec chmod 775 {} \; \
    && find /var/www/html/public/attachments -type f -exec chmod 664 {} \;

# Start PHP-FPM server
CMD ["php-fpm"]