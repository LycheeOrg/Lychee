FROM debian:bookworm-slim AS base

# Set version label
LABEL maintainer="lycheeorg"

# Environment variables
ENV PUID='1000'
ENV PGID='1000'
ENV USER='lychee'
ENV PHP_TZ=UTC

# Install base dependencies, add user and group, clone the repo and install php libraries
RUN \
    set -ev && \
    [ "$TARGET" != "release" -o "$BRANCH" = "master" ] && \
    apt-get update && \
    apt-get upgrade -qy && \
    apt-get install -qy --no-install-recommends\
    adduser \
    nginx-light \
    php8.2-mysql \
    php8.2-pgsql \
    php8.2-sqlite3 \
    php8.2-imagick \
    php8.2-mbstring \
    php8.2-gd \
    php8.2-xml \
    php8.2-zip \
    php8.2-fpm \
    php8.2-redis \
    php8.2-bcmath \
    php8.2-intl \
    curl \
    libimage-exiftool-perl \
    ffmpeg \
    git \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    webp \
    cron \
    composer \
    unzip && \
    addgroup --gid "$PGID" "$USER" && \
    adduser --gecos '' --no-create-home --disabled-password --uid "$PUID" --gid "$PGID" "$USER" && \
    mkdir -p /var/www/html/Lychee
	
WORKDIR /var/www/html/Lychee

COPY composer.json .
COPY composer.lock .

RUN composer install --download-only --no-autoloader --no-scripts

COPY .github/workflows/.env.sqlite .env
COPY phpstan ./phpstan
COPY bootstrap ./bootstrap
COPY config ./config
COPY lang ./lang
COPY database ./database
COPY routes ./routes

COPY resources ./resources
COPY tests ./tests
COPY app ./app

COPY artisan .
COPY index.php .
COPY Makefile .
COPY phpstan.neon .
COPY phpunit.xml .
COPY simple_error_template.html .

RUN composer install

RUN php artisan migrate:fresh

RUN make test