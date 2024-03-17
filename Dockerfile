FROM debian:bookworm-slim as base

# Set version label
LABEL maintainer="lycheeorg"

# Environment variables
ENV PUID='1000'
ENV PGID='1000'
ENV USER='lychee'
ENV PHP_TZ=UTC

# Arguments
VOLUME /var/www/html/Lychee

# Install base dependencies, add user and group, clone the repo and install php libraries
RUN \
    set -ev && \
    apt-get update && \
    apt-get upgrade -qy && \
    apt-get install -qy --no-install-recommends\
    adduser \
	bash \
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
	curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
	apt-get install -y nodejs && \
    addgroup --gid "$PGID" "$USER" && \
    adduser --gecos '' --no-create-home --disabled-password --uid "$PUID" --gid "$PGID" "$USER"	&& \
	curl https://raw.githubusercontent.com/LycheeOrg/Lychee-Docker/master/default.conf -o /etc/nginx/nginx.conf && \
    echo "\
    #!/bin/sh\n\
    echo \"Starting services...\"\n\
    service php8.2-fpm start\n\
    nginx &\n\
    echo \"Ready.\"\n\
    tail -s 1 /var/log/nginx/*.log -f\n\
    " > /start.sh

WORKDIR /var/www/html/Lychee
RUN if [ ! -e /run/php ] ; then mkdir /run/php ; fi

EXPOSE 80 5173

HEALTHCHECK CMD curl --fail http://localhost:80/ || exit 1

CMD ["sh", "/start.sh"]
