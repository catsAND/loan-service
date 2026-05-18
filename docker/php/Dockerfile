FROM dunglas/frankenphp:1.12.2-php8-alpine AS base

RUN apk add --no-cache \
    bash \
    curl \
    git \
    unzip \
    zip \
    libpq-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    linux-headers \
    $PHPIZE_DEPS

RUN install-php-extensions \
    pdo \
    pdo_pgsql \
    pgsql \
    redis \
    amqp \
    zip \
    intl \
    opcache \
    mbstring \
    xml \
    ctype \
    iconv \
    tokenizer \
    simplexml \
    bcmath \
    pcntl \
    sockets

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.alpine.sh' | bash \
    && apk add --no-cache symfony-cli

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

COPY docker/php/Caddyfile /etc/caddy/Caddyfile

WORKDIR /app

FROM base AS dev

ENV APP_ENV=dev
ENV XDEBUG_MODE=debug

RUN install-php-extensions xdebug

EXPOSE 80

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
