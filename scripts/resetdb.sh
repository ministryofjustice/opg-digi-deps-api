#!/usr/bin/env bash
set -e
# We need below to create the params file on container start
run-parts /etc/my_init.d

. /var/www/scripts/initialize_schema.sh

php app/console doctrine:migrations:status-check
php app/console doctrine:migrations:migrate --no-interaction -vvv
php app/console doctrine:fixtures:load --no-interaction
