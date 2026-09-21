FROM php:8.3-apache

# ═══════════════════════════════════════════════════════════════════
# Install system dependencies
# ═══════════════════════════════════════════════════════════════════
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    postgresql-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ═══════════════════════════════════════════════════════════════════
# Configure GD (with JPEG + FreeType)
# ═══════════════════════════════════════════════════════════════════
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

# ═══════════════════════════════════════════════════════════════════
# Install PHP extensions
# ═══════════════════════════════════════════════════════════════════
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    zip \
    gd \
    bcmath \
    exif \
    pcntl

# ═══════════════════════════════════════════════════════════════════
# Install Composer
# ═══════════════════════════════════════════════════════════════════
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ═══════════════════════════════════════════════════════════════════
# Copy project
# ═══════════════════════════════════════════════════════════════════
COPY . .

# ═══════════════════════════════════════════════════════════════════
# Install PHP dependencies
# ═══════════════════════════════════════════════════════════════════
RUN composer install --no-dev --optimize-autoloader --no-interaction

# ═══════════════════════════════════════════════════════════════════
# Set permissions
# ═══════════════════════════════════════════════════════════════════
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p storage/app/backups \
    && chown -R www-data:www-data storage/app/backups

# ═══════════════════════════════════════════════════════════════════
# Enable Apache mod_rewrite
# ═══════════════════════════════════════════════════════════════════
RUN a2enmod rewrite

# ═══════════════════════════════════════════════════════════════════
# Copy Apache config
# ═══════════════════════════════════════════════════════════════════
COPY .render/apache.conf /etc/apache2/sites-available/000-default.conf

# ═══════════════════════════════════════════════════════════════════
# Verify pg_dump is available
# ═══════════════════════════════════════════════════════════════════
RUN which pg_dump && pg_dump --version

EXPOSE 80

CMD ["apache2-foreground"]
