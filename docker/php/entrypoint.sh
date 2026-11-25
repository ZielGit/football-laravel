#!/bin/sh
# Prepara el proyecto automáticamente. Solo el servicio "app" instala (RUN_SETUP=true);
# queue y scheduler esperan a que termine para no pelear por vendor/ ni por las migraciones.
set -e
cd /var/www/html

if [ "$RUN_SETUP" = "true" ]; then
  rm -f storage/app/.setup-done

  [ -f .env ] || cp .env.docker .env

  if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
  fi

  grep -q '^APP_KEY=.\+' .env || php artisan key:generate --force

  # Passport: migraciones (se publican una sola vez) y llaves de firma de tokens.
  if ! ls database/migrations | grep -q 'create_oauth_auth_codes_table'; then
    php artisan vendor:publish --tag=passport-migrations
  fi
  [ -f storage/oauth-private.key ] || php artisan passport:keys --force

  # Espera a MySQL reintentando la migración.
  tries=0
  until php artisan migrate --force; do
    tries=$((tries + 1))
    [ "$tries" -ge 30 ] && { echo "MySQL no respondió a tiempo"; exit 1; }
    echo "Esperando a MySQL... ($tries/30)"
    sleep 3
  done

  # Cliente personal de Passport (no interactivo).
  php artisan passport:client --personal --name="Football API" --no-interaction

  php artisan db:seed --force
  touch storage/app/.setup-done
else
  while [ ! -f storage/app/.setup-done ]; do
    echo "Esperando a que el servicio app termine la instalación..."
    sleep 3
  done
fi

exec "$@"
