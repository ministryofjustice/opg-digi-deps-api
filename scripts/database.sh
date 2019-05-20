#!/bin/bash
echo "This script is deprecated. Use migrate.sh instead"
set -e
#let's configure environment
confd -onetime -backend env

cd /var/www
php app/console doctrine:migrations:status-check
php app/console doctrine:migrations:migrate --no-interaction -vvv
php app/console doctrine:fixtures:load --no-interaction
