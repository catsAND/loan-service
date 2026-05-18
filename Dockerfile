FROM dunglas/frankenphp:1.12.2-php8-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libpq-dev \
    libxml2-dev \
    libzip-dev \
    linux-headers \
    nodejs \
    npm \
    oniguruma-dev \
    rabbitmq-c-dev \
    unzip \
    zip \
    $PHPIZE_DEPS

RUN install-php-extensions \
    amqp \
    bcmath \
    ctype \
    iconv \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo \
    pdo_pgsql \
    pgsql \
    simplexml \
    sockets \
    tokenizer \
    xml \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
COPY docker/php/Caddyfile /etc/caddy/Caddyfile
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint

RUN composer dump-autoload --no-interaction --optimize \
    && mkdir -p var/cache var/log var/sessions \
    && chmod +x /usr/local/bin/docker-entrypoint \
    && chmod -R 775 var

ENV APP_ENV=prod \
    APP_DEBUG=0 \
    SERVER_NAME=:80

EXPOSE 80

ENTRYPOINT ["docker-entrypoint"]
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
