#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
  if [ ! -f storage/app/.app_key ]; then
    php -r 'echo "base64:" . base64_encode(random_bytes(32));' > storage/app/.app_key
  fi
  APP_KEY=$(cat storage/app/.app_key)
  export APP_KEY
fi

php artisan migrate --force
php artisan db:seed --force

exec php artisan serve --host=0.0.0.0 --port=8000
