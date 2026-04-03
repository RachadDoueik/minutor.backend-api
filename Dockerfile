FROM webdevops/php-nginx:8.2-alpine AS vendor
WORKDIR /app
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts

FROM webdevops/php-nginx:8.2-alpine
WORKDIR /app

COPY . /app
COPY --from=vendor /app/vendor /app/vendor

RUN set -eux; \
    mkdir -p /app/storage /app/bootstrap/cache; \
    chown -R application:application /app/storage /app/bootstrap/cache; \
    chmod -R ug+rwx /app/storage /app/bootstrap/cache

ENV WEB_DOCUMENT_ROOT=/app/public
