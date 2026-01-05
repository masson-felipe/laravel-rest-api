#!/bin/sh
set -e

cd /var/www

if [ ! -d vendor ]; then
  echo "📦 Instalando dependências (composer install)..."
  composer install --no-interaction --prefer-dist
fi

if [ ! -f .env ]; then
  echo "⚙️ Criando .env"
  cp .env.example .env
fi

if ! grep -q "APP_KEY=base64" .env; then
  echo "🔑 Gerando APP_KEY"
  php artisan key:generate --force
fi

echo "🚀 PHP-FPM iniciado"
exec php-fpm
