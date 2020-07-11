#!/bin/sh
set -e

echo "Waiting for db to be ready..."
until bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    sleep 1
done

bin/console doctrine:ensure-production-settings
bin/console doctrine:migrations:migrate --no-interaction

exec docker-php-entrypoint "$@"
