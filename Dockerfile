# ═══════════════════════════════════════════════════════════════════
# استخدام Bookworm كقاعدة (لأن PGDG يدعمها بشكل كامل)
# ═══════════════════════════════════════════════════════════════════
FROM php:8.3-apache-bookworm

# ═══════════════════════════════════════════════════════════════════
# 1. إضافة مستودع PGDG الرسمي
# ═══════════════════════════════════════════════════════════════════
RUN apt-get update && apt-get install -y \
    curl \
    ca-certificates \
    gnupg \
    && curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc \
        | gpg --dearmor -o /usr/share/keyrings/postgresql.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/postgresql.gpg] http://apt.postgresql.org/pub/repos/apt bookworm-pgdg main" \
        > /etc/apt/sources.list.d/pgdg.list

# ═══════════════════════════════════════════════════════════════════
# 2. تثبيت المكتبات + PostgreSQL 18 client
# ═══════════════════════════════════════════════════════════════════
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    postgresql-client-18 \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# ═══════════════════════════════════════════════════════════════════
# 3. إعداد GD
# ═══════════════════════════════════════════════════════════════════
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

# ═══════════════════════════════════════════════════════════════════
# 4. تثبيت extensions
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
# 5. Composer
# ═══════════════════════════════════════════════════════════════════
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

# ═══════════════════════════════════════════════════════════════════
# 6. الصلاحيات + مجلد النسخ الاحتياطي
# ═══════════════════════════════════════════════════════════════════
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && mkdir -p storage/app/backups \
    && chown -R www-data:www-data storage/app/backups

RUN a2enmod rewrite

COPY .render/apache.conf /etc/apache2/sites-available/000-default.conf

# ═══════════════════════════════════════════════════════════════════
# 7. التحقق من pg_dump
# ═══════════════════════════════════════════════════════════════════
RUN which pg_dump && pg_dump --version

EXPOSE 80

CMD ["apache2-foreground"]
