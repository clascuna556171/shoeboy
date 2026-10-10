# ========================================================
# Stage 1: Build Frontend Assets (Vite, Tailwind, Fonts)
# ========================================================
FROM node:20-alpine AS frontend
WORKDIR /app

# Install npm dependencies
COPY package*.json ./
RUN npm install

# Build compiled assets
COPY . .
RUN npm run build

# ========================================================
# Stage 2: Production PHP 8.3 & Nginx Web Server
# ========================================================
FROM serversideup/php:8.3-fpm-nginx

# Configure Nginx & PHP
ENV WEB_DOCUMENT_ROOT=/var/www/html/public
ENV PHP_OPCACHE_ENABLE=1

WORKDIR /var/www/html

# Ensure working directory ownership for www-data
USER root
RUN mkdir -p /var/www/html && chown -R www-data:www-data /var/www/html

# Switch to unprivileged user to run Composer
USER www-data

# Copy composer files first for optimal Docker layer caching
COPY --chown=www-data:www-data composer.json composer.lock ./

# Install production dependencies (without scripts and autoloader)
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader

# Copy application files
COPY --chown=www-data:www-data . .

# Copy compiled frontend assets from Stage 1
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

# Generate final optimized production classmap autoloader
RUN composer dump-autoload --optimize --no-dev --no-interaction

# Configure storage/cache permissions and entrypoint script
USER root
RUN chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

COPY --chmod=755 docker/entrypoint.sh /etc/entrypoint.d/99-init.sh

# Revert to unprivileged user for runtime security
USER www-data
