#!/bin/bash

cd /app
/sbin/setuser app php app/console doctrine:fixtures:load --append