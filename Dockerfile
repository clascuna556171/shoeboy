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

# Copy project files with proper web user permissions
COPY --chown=9999:9999 . .

# Copy compiled frontend assets from Stage 1
COPY --from=frontend --chown=9999:9999 /app/public/build ./public/build

# Install production Composer dependencies (without dev packages)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set up storage and cache write permissions
RUN chmod -R 775 storage bootstrap/cache

# Register entrypoint initialization script
COPY --chmod=755 docker/entrypoint.sh /etc/entrypoint.d/99-init.sh
