FROM php:8.3-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    unzip \
    git \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        xml \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set Apache document root to Laravel's public/
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite

WORKDIR /var/www/html

# Copy application code
COPY refactored/ .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# PHP opcache configuration for production
RUN echo "opcache.enable=1\nopcache.memory_consumption=128\nopcache.interned_strings_buffer=8\nopcache.max_accelerated_files=4000\nopcache.revalidate_freq=2" \
    > /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 80
