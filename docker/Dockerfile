FROM php:8.4.14-fpm-bookworm

ARG WWWUSER=1000
ARG WWWGROUP=1000

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN set -eux; \
    apt-get update \
    && apt-get install -y --no-install-recommends \
        autoconf \
        curl \
        g++ \
        git \
        libfreetype6-dev \
        libcurl4-openssl-dev \
        libicu-dev \
        libonig-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libpq-dev \
        libwebp-dev \
        libxml2-dev \
        libzip-dev \
        make \
        pkg-config \
        unzip \
    && rm -rf /var/lib/apt/lists/*

RUN set -eux; \
    docker-php-ext-configure intl

RUN set -eux; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp

RUN set -eux; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        exif \
        dom \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        xml \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN groupadd --force -g "${WWWGROUP}" laravel \
    && useradd --create-home --shell /bin/bash --no-user-group -g "${WWWGROUP}" -u "${WWWUSER}" laravel

RUN sed -i 's/^user = .*/user = laravel/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^group = .*/group = laravel/' /usr/local/etc/php-fpm.d/www.conf

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

COPY . .

RUN mkdir -p storage/framework/cache \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        bootstrap/cache \
    && chown -R laravel:laravel storage bootstrap/cache

RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

COPY docker/php/conf.d/laravel.ini /usr/local/etc/php/conf.d/99-laravel.ini

CMD ["php-fpm"]
