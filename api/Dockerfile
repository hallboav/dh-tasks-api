FROM php:7.4-fpm-alpine AS php_fpm

RUN apk add --no-cache --update \
        rabbitmq-c-dev \
        fcgi \
    ;

RUN set -eux; \
    apk add --no-cache --update --virtual .build-deps \
        $PHPIZE_DEPS \
    ; \
    pecl install \
        amqp \
    ; \
    pecl clear-cache; \
    docker-php-ext-install -j$(nproc) \
        pdo_mysql \
    ; \
    docker-php-ext-enable \
        opcache \
        amqp \
    ; \
    apk del .build-deps

RUN set -eux; \
    ln -s $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini; \
    { \
        echo 'date.timezone=Europe/Luxembourg'; \
        echo 'session.auto_start=Off'; \
        echo 'short_open_tag=Off'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=20000'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'realpath_cache_size=4096K'; \
        echo 'realpath_cache_ttl=600'; \
    } | tee $PHP_INI_DIR/conf.d/perf.ini

RUN set -eux; \
    { \
        echo '[www]'; \
        echo 'ping.path = /ping'; \
    } | tee /usr/local/etc/php-fpm.d/docker-healthcheck.conf

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -eux; \
    composer global require "symfony/flex" --prefer-dist --no-progress --no-suggest --classmap-authoritative; \
    composer clear-cache
ENV PATH="${PATH}:/root/.composer/vendor/bin"

COPY composer.json composer.lock symfony.lock ./
RUN set -eux; \
    composer install --prefer-dist --no-dev --no-scripts --no-progress --no-suggest; \
    composer clear-cache

COPY .env ./
RUN composer dump-env prod; \
    rm .env

COPY bin bin/
COPY config config/
COPY public public/
COPY migrations migrations/
COPY src src/

ENV APP_ENV=prod
RUN set -eux; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    composer run-script --no-dev post-install-cmd; \
    chmod +x bin/console; sync; \
    bin/console about

COPY docker/php-fpm/healthcheck.sh /usr/local/bin/healthcheck
RUN chmod +x /usr/local/bin/healthcheck
HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD ["healthcheck"]

COPY docker/php-fpm/entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

FROM nginx:1.19-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=php_fpm /app /app
