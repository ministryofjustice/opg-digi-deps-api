#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d

export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=digideps_unit_test}
export PGUSER=${API_DATABASE_USERNAME:=api}

cd /var/www
# clear cache
rm -rf var/*

rm -f /tmp/dd_stats.csv
rm -f /tmp/dd_stats.unittest.csv
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Controller/
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Controller-Report/
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Controller-Ndr/
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Service/
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Entity/
php vendor/phpunit/phpunit/phpunit -c tests/phpunit.xml tests/AppBundle/Transformer/
