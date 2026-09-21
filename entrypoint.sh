#!/bin/bash
set -e

echo "🚀 Starting MiraclePOS..."

# ═══════════════════════════════════════════════════════════════════
# Fix permissions
# ═══════════════════════════════════════════════════════════════════
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# ═══════════════════════════════════════════════════════════════════
# Create backups directory
# ═══════════════════════════════════════════════════════════════════
mkdir -p /var/www/html/storage/app/backups
chown -R www-data:www-data /var/www/html/storage/app/backups

# ═══════════════════════════════════════════════════════════════════
# Run migrations (idempotent)
# ═══════════════════════════════════════════════════════════════════
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "📦 Running migrations..."
    php artisan migrate --force
fi

# ═══════════════════════════════════════════════════════════════════
# Run seeders (idempotent - only if enabled)
# ═══════════════════════════════════════════════════════════════════
if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo "🌱 Running seeders..."
    php artisan db:seed --class=CoreDataSeeder --force
fi

# ═══════════════════════════════════════════════════════════════════
# Clear caches
# ═══════════════════════════════════════════════════════════════════
echo "🧹 Clearing caches..."
php artisan cache:clear 2>/dev/null || true
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# ═══════════════════════════════════════════════════════════════════
# Cache config (production)
# ═══════════════════════════════════════════════════════════════════
echo "⚡ Caching config..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ═══════════════════════════════════════════════════════════════════
# Verify pg_dump
# ═══════════════════════════════════════════════════════════════════
echo "🔍 Checking pg_dump..."
which pg_dump && pg_dump --version || echo "⚠️ pg_dump not found"

# ═══════════════════════════════════════════════════════════════════
# Start Apache
# ═══════════════════════════════════════════════════════════════════
echo "✅ Starting Apache..."
exec apache2-foreground
