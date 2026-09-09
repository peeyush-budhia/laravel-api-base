# syntax=docker/dockerfile:1

FROM php:8.4.14-fpm-bookworm AS php-extension-builder

ARG PHPREDIS_VERSION=6.2.0
ARG PHPREDIS_SHA256=470333b27cbf9485d36b610b81300c06491a6575f22c6801a9cefc55285ed123

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        autoconf \
        g++ \
        libfreetype6-dev \
        libicu-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        libzip-dev \
        make \
        pkg-config \
        unzip; \
    docker-php-ext-configure intl; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        pcntl \
        pdo_mysql \
        zip; \
    curl -fsSL "https://github.com/phpredis/phpredis/archive/refs/tags/${PHPREDIS_VERSION}.tar.gz" -o /tmp/phpredis.tar.gz; \
    echo "${PHPREDIS_SHA256}  /tmp/phpredis.tar.gz" | sha256sum -c -; \
    mkdir /tmp/phpredis; \
    tar -xzf /tmp/phpredis.tar.gz --strip-components=1 -C /tmp/phpredis; \
    cd /tmp/phpredis; \
    phpize; \
    ./configure --enable-redis; \
    make -j"$(nproc)"; \
    make install; \
    docker-php-ext-enable redis; \
    cd /var/www/html; \
    rm -rf /tmp/phpredis /tmp/phpredis.tar.gz /var/lib/apt/lists/*

FROM php:8.4.14-fpm-bookworm AS php-base

ARG WWWUSER=1000
ARG WWWGROUP=1000

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libfcgi-bin \
        libfreetype6 \
        libicu72 \
        libjpeg62-turbo \
        libpng16-16 \
        libwebp7 \
        libzip4 \
        unzip; \
    apt-get purge -y --auto-remove ${PHPIZE_DEPS}; \
    rm -rf /var/lib/apt/lists/*

COPY --from=php-extension-builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-extension-builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

RUN groupadd --force -g "${WWWGROUP}" laravel \
    && useradd --create-home --shell /bin/sh --no-user-group -g "${WWWGROUP}" -u "${WWWUSER}" laravel \
    && sed -i 's/^user = .*/user = laravel/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^group = .*/group = laravel/' /usr/local/etc/php-fpm.d/www.conf \
    && printf '\nping.path = /ping\n' >> /usr/local/etc/php-fpm.d/www.conf

COPY docker/php/conf.d/laravel.ini /usr/local/etc/php/conf.d/99-laravel.ini

HEALTHCHECK --interval=10s --timeout=3s --start-period=20s --retries=3 \
    CMD SCRIPT_NAME=/ping SCRIPT_FILENAME=/ping REQUEST_METHOD=GET cgi-fcgi -bind -connect 127.0.0.1:9000 | grep -q pong || exit 1

FROM php-base AS development

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-progress --no-scripts

COPY . .

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        bootstrap/cache \
    && composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && chown -R laravel:laravel storage bootstrap/cache

USER laravel

CMD ["php-fpm", "-F"]

FROM php-base AS production-vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-progress \
    --prefer-dist \
    --classmap-authoritative

COPY . .

RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        bootstrap/cache \
    && composer dump-autoload --no-dev --classmap-authoritative --no-scripts \
    && php artisan package:discover --ansi \
    && ln -s ../storage/app/public public/storage \
    && chown -R laravel:laravel storage bootstrap/cache

FROM php-base AS production

ENV APP_ENV=production
ENV APP_DEBUG=false

COPY docker/php/conf.d/production.ini /usr/local/etc/php/conf.d/zz-production.ini
COPY --from=production-vendor --chown=laravel:laravel /var/www/html /var/www/html

USER laravel

CMD ["php-fpm", "-F"]

FROM nginx:1.27-alpine AS web

COPY docker/nginx/production.conf /etc/nginx/conf.d/default.conf
COPY --from=production /var/www/html/public /var/www/html/public

EXPOSE 80

HEALTHCHECK --interval=10s --timeout=3s --start-period=20s --retries=3 \
    CMD wget --quiet --tries=1 --spider http://127.0.0.1/up || exit 1
