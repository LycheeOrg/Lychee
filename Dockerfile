# Lychee - Laravel Backend Dockerfile
# Multi-stage build with Laravel Octane + FrankenPHP
ARG NODE_ENV=production

# ============================================================================
# Stage 1: Composer Dependencies
# ============================================================================
FROM composer:2.8@sha256:5248900ab8b5f7f880c2d62180e40960cd87f60149ec9a1abfd62ac72a02577c AS composer

WORKDIR /app

# Copy composer files first for layer caching
COPY composer.json composer.lock ./

# Install dependencies (no dev packages for production)
# Remove markdown and test directories to slim down the image
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-reqs \
    && find vendor \
    \( -iname "*.md" -o -iname "test" -o -iname "tests" \) \
    -exec rm -rf {} +

# ============================================================================
# Stage 2: Node.js Build for Frontend Assets
# ============================================================================
FROM node:20-alpine@sha256:658d0f63e501824d6c23e06d4bb95c71e7d704537c9d9272f488ac03a370d448 AS node

# Build argument to control dev vs production build
ARG NODE_ENV
ENV NODE_ENV=$NODE_ENV

WORKDIR /app

# Copy package files for layer caching
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci --no-audit

# Copy frontend source
COPY resources/ ./resources/
COPY public/ ./public/
COPY lang/ ./lang/
COPY vite.config.ts vite.embed.config.ts tsconfig.json ./

# Build frontend assets
# When NODE_ENV=development, Vite sets import.meta.env.DEV=true
RUN npm run build

# ============================================================================
# Stage 3: Production FrankenPHP Image
# ============================================================================
FROM dunglas/frankenphp:php8.5-trixie@sha256:f66466767d1c95587621a8ffb7912dea1165fa45e1c2cdcd0997f872d037b96f

ARG USER=appuser

LABEL maintainer="lycheeorg"
LABEL org.opencontainers.image.title="Lychee"
LABEL org.opencontainers.image.description="Self-hosted photo management system done right."
LABEL org.opencontainers.image.authors="LycheeOrg"
LABEL org.opencontainers.image.vendor="LycheeOrg"
LABEL org.opencontainers.image.source="https://github.com/LycheeOrg/Lychee"
LABEL org.opencontainers.image.url="https://lycheeorg.github.io"
LABEL org.opencontainers.image.documentation="https://lycheeorg.dev/docs"
LABEL org.opencontainers.image.licenses="MIT"
LABEL org.opencontainers.image.base.name="dunglas/frankenphp:php8.5-trixie"

# Install system utilities and PHP extensions
# hadolint ignore=DL3008,DL3009
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    linux-libc-dev \
    libimage-exiftool-perl \
    ffmpeg \
    imagemagick \
    jpegoptim \
    procps \
    netcat-openbsd \
    unzip \
    curl \
    bash \
    gosu \
	ghostscript \
	&& sed -i '/<\/policymap>/i \  <policy domain="coder" rights="read|write" pattern="PDF" \/>' /etc/ImageMagick-7/policy.xml \
    && install-php-extensions \
    pdo_mysql \
    pdo_pgsql \
    gd \
    zip \
    bcmath \
    ldap \
    sodium \
    opcache \
    pcntl \
    exif \
    intl \
    imagick \
    redis \
	&& apt-get clean -qy \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy application code
COPY --chown=www-data:www-data . .

# Copy vendor from composer stage
COPY --from=composer --chown=www-data:www-data /app/vendor ./vendor

# Copy built frontend assets from node stage
COPY --from=node --chown=www-data:www-data /app/public/build ./public/build
COPY --from=node --chown=www-data:www-data /app/public/embed ./public/embed

# Ensure storage and bootstrap/cache are writable with minimal permissions
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public/dist \
    && chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 777 storage bootstrap/cache \
    && chmod -R 775 public/dist \
    && touch /app/frankenphp_target \
    && touch /app/public/dist/user.css \
    && touch /app/public/dist/custom.js \
    && chown www-data:www-data /app/public/dist/user.css /app/public/dist/custom.js \
    && chmod 644 /app/public/dist/user.css /app/public/dist/custom.js \
    && cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini \
    && echo "upload_max_filesize=128M" > $PHP_INI_DIR/conf.d/custom.ini \
    && echo "post_max_size=128M" >> $PHP_INI_DIR/conf.d/custom.ini \
    && echo "memory_limit=\${PHP_MEMORY_LIMIT:-1024M}" >> $PHP_INI_DIR/conf.d/custom.ini \
    && echo "max_execution_time=\${PHP_MAX_EXECUTION_TIME:-3000}" >> $PHP_INI_DIR/conf.d/custom.ini \
    && echo "expose_php=Off" >> $PHP_INI_DIR/conf.d/custom.ini \
    && echo "display_errors=Off" >> $PHP_INI_DIR/conf.d/custom.ini \
    && echo "log_errors=On" >> $PHP_INI_DIR/conf.d/custom.ini

# Copy entrypoint and validation scripts
COPY docker/scripts/00-conf-check.sh /usr/local/bin/00-conf-check.sh
COPY docker/scripts/01-validate-env.sh /usr/local/bin/01-validate-env.sh
COPY docker/scripts/02-dump-env.sh /usr/local/bin/02-dump-env.sh
COPY docker/scripts/03-db-check.sh /usr/local/bin/03-db-check.sh
COPY docker/scripts/04-user-setup.sh /usr/local/bin/04-user-setup.sh
COPY docker/scripts/05-permissions-check.sh /usr/local/bin/05-permissions-check.sh
COPY docker/scripts/create-admin-user.sh /usr/local/bin/create-admin-user.sh
COPY docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/00-conf-check.sh \
    /usr/local/bin/01-validate-env.sh \
    /usr/local/bin/02-dump-env.sh \
    /usr/local/bin/03-db-check.sh \
    /usr/local/bin/04-user-setup.sh \
    /usr/local/bin/05-permissions-check.sh \
    /usr/local/bin/create-admin-user.sh \
    /usr/local/bin/entrypoint.sh \
    && mkdir -p /data /config \
    && chmod -R 775 /data /config

# Expose port 8000 (Octane)
EXPOSE 8000

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default command: run Octane with FrankenPHP
# Container mode is controlled by LYCHEE_MODE environment variable:
# - "web" (default): Runs FrankenPHP/Octane web server (this CMD)
# - "worker": Runs Laravel queue worker (entrypoint.sh overrides CMD)
# See docs/specs/2-how-to/deploy-worker-mode.md for deployment guide
CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=8000"]