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
FROM dunglas/frankenphp:php8.4-alpine@sha256:49654aea8f2b9bc225bde6d89c9011054505ca2ed3e9874b251035128518b491

ARG USER=appuser

LABEL maintainer="lycheeorg"
LABEL org.opencontainers.image.source="https://github.com/LycheeOrg/Lychee"

# Install system utilities and PHP extensions
# hadolint ignore=DL3018
RUN apk add --no-cache \
    exiftool \
    shadow \
    ffmpeg \
    gd \
    grep \
    imagemagick \
    jpegoptim \
    netcat-openbsd \
    unzip \
    curl \
    && install-php-extensions \
    pdo_mysql \
    pdo_pgsql \
    gd \
    zip \
    bcmath \
    sodium \
    opcache \
    pcntl \
    exif \
    imagick \
    intl \
    redis \
    tokenizer \
    && rm -rf /var/cache/apk/*

WORKDIR /app

# Copy application code
COPY --chown=www-data:www-data . .

# Copy vendor from composer stage
COPY --from=composer --chown=www-data:www-data /app/vendor ./vendor

# Copy built frontend assets from node stage
COPY --from=node --chown=www-data:www-data /app/public/build ./public/build

# Ensure storage and bootstrap/cache are writable with minimal permissions
RUN mkdir -p storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public/dist \
    && chown -R www-data:www-data storage bootstrap/cache public/dist \
    && chmod -R 750 storage bootstrap/cache \
    && chmod -R 755 public/dist \
    && touch /app/frankenphp_target \
    && touch /app/public/dist/user.css \
    && touch /app/public/dist/custom.js \
    && chown www-data:www-data /app/public/dist/user.css /app/public/dist/custom.js \
    && chmod 644 /app/public/dist/user.css /app/public/dist/custom.js \
    && cp $PHP_INI_DIR/php.ini-production $PHP_INI_DIR/php.ini \
    && echo "upload_max_filesize=110M" > $PHP_INI_DIR/conf.d/custom.ini \
    && echo "post_max_size=110M" >> $PHP_INI_DIR/conf.d/custom.ini \
    && echo "max_execution_time=3000" >> $PHP_INI_DIR/conf.d/custom.ini \
    && echo "expose_php=Off" >> $PHP_INI_DIR/conf.d/custom.ini \
    && echo "display_errors=Off" >> $PHP_INI_DIR/conf.d/custom.ini \
    && echo "log_errors=On" >> $PHP_INI_DIR/conf.d/custom.ini

# Copy entrypoint and validation scripts
COPY docker/scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY docker/scripts/validate-env.sh /usr/local/bin/validate-env.sh
COPY docker/scripts/create-admin-user.sh /usr/local/bin/create-admin-user.sh
COPY docker/scripts/permissions-check.sh /usr/local/bin/permissions-check.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/validate-env.sh /usr/local/bin/permissions-check.sh /usr/local/bin/create-admin-user.sh

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