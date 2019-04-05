#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d

cd /app

# the following are not needed, as `run-parts` above already calls migration scripts
# to remove after next release in February
/sbin/setuser app php app/console doctrine:migrations:status-check
/sbin/setuser app php app/console doctrine:migrations:migrate --no-interaction -vvv

# add default users
/sbin/setuser app php app/console doctrine:fixtures:load --no-interaction

# add missing data potentially notmissing due to failing migrations or previous bugs on data listeners. Slow, only enable if/when needed
# /sbin/setuser app php app/console digideps:fix-data --env=prod
