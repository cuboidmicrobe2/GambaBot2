FROM php:8.5-cli

# System
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    pkg-config \
    libssl-dev \
    libcurl4-openssl-dev \
    libev-dev \
    libonig-dev \
    default-libmysqlclient-dev \
    && rm -rf /var/lib/apt/lists/*

# Required PHP extensions
RUN docker-php-ext-install \
    pcntl \
    posix \
    sockets \
    mbstring \
    pdo_mysql \
    mysqli

# Recommended event-loop extension (stable on PHP 8.5)
RUN pecl install ev \
    && docker-php-ext-enable ev

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-interaction --optimize-autoloader

CMD ["php", "main.php"]