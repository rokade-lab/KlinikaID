# syntax=docker/dockerfile:1

ARG PHP_IMAGE=php:8.4.27-fpm-alpine@sha256:15462d03828e68ae806c552403360edc5269a830d358646a9564dbebe632b787
ARG COMPOSER_IMAGE=composer:2.11.1@sha256:8de2184c6de224f4689e8f80ed5f5edaf0030b6b8b7839a22a2bcb6722ac87d0

FROM ${COMPOSER_IMAGE} AS composer

FROM ${PHP_IMAGE} AS base
ARG APP_UID=10001
ARG APP_GID=10001
RUN set -eux; \
    apk add --no-cache libpq; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev; \
    docker-php-ext-install -j"$(nproc)" pdo_pgsql; \
    docker-php-ext-enable opcache; \
    apk del --no-network .build-deps; \
    rm -rf /tmp/pear ~/.pearrc; \
    php -m | grep -qi '^pdo_pgsql$'; \
    php -m | grep -qi '^Zend OPcache$'
RUN set -eux; \
    cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"; \
    if ! getent group "$APP_GID" >/dev/null; then addgroup -S -g "$APP_GID" app; fi; \
    adduser -S -D -H -u "$APP_UID" -G "$(getent group "$APP_GID" | cut -d: -f1)" -h /app -s /sbin/nologin app; \
    mkdir -p /app /var/lib/klinikaid/storage /var/log/klinikaid; \
    chown "$APP_UID:$APP_GID" /var/lib/klinikaid/storage /var/log/klinikaid; \
    chmod 0750 /var/lib/klinikaid/storage; \
    chmod 0755 /var/log/klinikaid
COPY docker/php/conf.d/zz-klinikaid.ini "$PHP_INI_DIR/conf.d/zz-klinikaid.ini"
COPY docker/php/php-fpm.d/zz-klinikaid.conf /usr/local/etc/php-fpm.d/zz-klinikaid.conf
WORKDIR /app

FROM base AS vendor
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_HOME=/tmp/composer COMPOSER_ALLOW_SUPERUSER=1
RUN apk add --no-cache unzip
COPY composer.json composer.lock ./
RUN composer validate --no-check-publish --no-interaction \
 && composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader
COPY src/ src/
RUN composer dump-autoload --no-dev --optimize --no-interaction

FROM base AS app
COPY --chown=root:root src/ /app/src/
COPY --chown=root:root public/ /app/public/
COPY --chown=root:root templates/ /app/templates/
COPY --chown=root:root config/ /app/config/
COPY --chown=root:root database/ /app/database/
COPY --chown=root:root VERSION /app/VERSION
COPY --from=vendor --chown=root:root /app/vendor/ /app/vendor/
USER app
EXPOSE 9000
STOPSIGNAL SIGQUIT
HEALTHCHECK --interval=10s --timeout=3s --start-period=5s --retries=5 \
  CMD ["php", "-r", "$s=@fsockopen('127.0.0.1', 9000, $e, $m, 2); exit($s ? 0 : 1);"]
CMD ["php-fpm", "--nodaemonize"]

