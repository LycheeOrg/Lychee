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
    ca-certificates \
    curl \
    apt-transport-https && \
    curl -sSLo /tmp/debsuryorg-archive-keyring.deb https://packages.sury.org/debsuryorg-archive-keyring.deb && \
    dpkg -i /tmp/debsuryorg-archive-keyring.deb && \
    sh -c 'echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ bookworm main" > /etc/apt/sources.list.d/php.list' && \
    curl -fsSL https://deb.nodesource.com/setup_23.x -o nodesource_setup.sh && \
    bash nodesource_setup.sh && \
    apt-get update && \
    apt-get install -qy --no-install-recommends \
    adduser \
    nginx-light \
    nodejs \
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

WORKDIR /var/www/html

RUN git clone --depth 1 https://github.com/LycheeOrg/Lychee.git Lychee

WORKDIR /var/www/html/Lychee

RUN chown -R www-data:www-data /var/www/html/Lychee
RUN chmod -R g+ws storage

RUN composer install --no-dev --prefer-dist

RUN npm ci

RUN rm -r storage/framework/cache/data/* 2> /dev/null || true && \
rm    storage/framework/sessions/* 2> /dev/null || true && \
rm    storage/framework/views/* 2> /dev/null || true && \
rm    storage/logs/* 2> /dev/null || true && \
chown -R www-data:www-data /var/www/html/Lychee && \
chmod -R g+ws storage/image-jobs || true && \
chmod -R g+ws storage/livewire-tmp || true && \
chmod -R g+ws storage/lychee-tmp || true

RUN curl https://raw.githubusercontent.com/LycheeOrg/Lychee-Docker/refs/heads/master/default.conf -o /etc/nginx/nginx.conf

EXPOSE 80 5173
VOLUME /conf /uploads /sym /logs /lychee-tmp

COPY entrypoint.sh /

COPY vite.config.ts /var/www/html/Lychee/vite.config.ts

HEALTHCHECK CMD curl --fail http://localhost:80/ || exit 1

RUN chmod +x /entrypoint.sh && \
    if [ ! -e /run/php ] ; then mkdir /run/php ; fi


ENTRYPOINT [ "/entrypoint.sh" ]

CMD [ "nginx" ]

# docker run -d \                      
#   -p 90:80 \
#   -p 5713:5713
#   -v ./lang/:/var/www/html/Lychee/lang \
#   lychee-test