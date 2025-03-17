FROM debian:bookworm-slim AS base

# Set version label
LABEL maintainer="lycheeorg"

# Environment variables
ENV PUID='1000'
ENV PGID='1000'
ENV USER='lychee'
ENV PHP_TZ=UTC

# Install base dependencies, add user and group, install php libraries
RUN \
    set -ev && \
    apt-get update && \
    apt-get upgrade -qy && \
    apt-get install -qy --no-install-recommends\
    ca-certificates \
    curl \
    apt-transport-https && \
    curl -sSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb && \
    dpkg -i /tmp/debsuryorg-archive-keyring.deb && \
    sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ bookworm main" > /etc/apt/sources.list.d/php.list' && \
    apt-get update && \
    apt-get install -qy --no-install-recommends \
    adduser \
    nginx-light \
    php8.4-mysql \
    php8.4-pgsql \
    php8.4-sqlite3 \
    php8.4-imagick \
    php8.4-mbstring \
    php8.4-gd \
    php8.4-xml \
    php8.4-zip \
    php8.4-fpm \
    php8.4-redis \
    php8.4-bcmath \
    php8.4-intl \
    libimage-exiftool-perl \
    ffmpeg \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    webp \
    unzip && \
    addgroup --gid "$PGID" "$USER" && \
    adduser --gecos '' --no-create-home --disabled-password --uid "$PUID" --gid "$PGID" "$USER"

COPY nginx.conf /etc/nginx/nginx.conf

COPY start.sh /start.sh
RUN chmod +x /start.sh

CMD ["/start.sh"]
