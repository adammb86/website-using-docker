FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    curl \
    libpng-dev \
    libxml2-dev \
    linux-headers

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    mbstring \
    gd \
    bcmath \
    opcache

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html