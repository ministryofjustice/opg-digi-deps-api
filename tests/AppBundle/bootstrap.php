<?php

//chdir(__DIR__ . '/www/');

require 'app/bootstrap.php.cache';

require __DIR__ . '/Fixtures.php';
require __DIR__ . '/MockeryStub.php';
require __DIR__ . '/Controller/AbstractTestController.php';

exec('php app/console cache:clear --env=test');
exec('php app/console doctrine:query:sql "DROP SCHEMA IF EXISTS public cascade; CREATE SCHEMA IF NOT EXISTS public;" --env=test');
exec('php app/console doctrine:migrations:migrate --no-interaction --env=test');
exec('php app/console digideps:fixtures  --env=test');
// additional check to see if doctrine schema is ok
exec('php app/console doctrine:schema:validate --env=test');

Fixtures::backupDb();
