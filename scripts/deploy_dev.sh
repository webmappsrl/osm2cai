#!/bin/bash
set -e

echo "Deployment started ..."

composer install
composer dump-autoload
php artisan config:clear
php artisan config:cache
php artisan clear-compiled
php artisan optimize
rm storage/app/database/last-dump.sql
php artisan db:download

psql -h 127.0.0.1 -U postgres -c "DROP DATABASE osm2cai"
psql -h 127.0.0.1 -U postgres -c "CREATE DATABASE osm2cai"
psql -h 127.0.0.1 -U postgres -d osm2cai -c "CREATE EXTENSION POSTGIS"
psql -h 127.0.0.1 -U postgres osm2cai < storage/app/database/last-dump.sql

php artisan migrate
php artisan osm2cai:setpasswords osm2cai

echo "Deployment finished!"