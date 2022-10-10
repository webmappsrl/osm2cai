#!/bin/bash

# ADD TO CRONTAB WITH THE FOLLOWING COMMAND
# bash /mnt/caiosm-data/update.sh > /mnt/caiosm-data/logs/$(date +%F).log 2>&1

# GLOBAL VARIABLES
LARAVEL_PATH=/var/www/html/laravel/osm2cai
OSM_PATH=/mnt/caiosm-data
PSQL_PASSWORD=T1tup4awmA!

function sitedown () {
    logit "osm2cai: setting osm2cai down during SYNC"
    echo "LARAVEL_PATH: $LARAVEL_PATH"

    cd $LARAVEL_PATH
    /usr/bin/php artisan down
}

function dbbackup () {
    logit "osm2cai: Cleaning and backup DB"
    echo "PGSQL_PASSWORD: $PSQL_PASSWORD"
    echo "LARAVEL_PATH: $LARAVEL_PATH"

    PGPASSWORD=$PSQL_PASSWORD psql -d osm2cai -U osm2cai -h localhost -c 'DELETE from hiking_routes_osm'
    cd $LARAVEL_PATH
    /usr/bin/php artisan osm2cai:dump_db
}

function osmblock () {
    logit "osmblock: with following parameters"
    echo "OSMPATH: $OSM_PATH"
    echo "PSQL_PASSWORD: $PSQL_PASSWORD"
    echo "LARAVEL_PATH: $LARAVEL_PATH"
    echo "PBF: $1"

    cd $OSM_PATH

    echo "Downloading PBF"
    wget --no-check-certificate -c -O last.pbf $1

    echo "Running osmconvert..."
    osmconvert last.pbf > last.o5m
    rm -f last.pbf

    echo "running osmfilter..."
    osmfilter last.o5m --keep-relations=route=hiking --hash-memory=3000 -o=hiking.o5m
    rm -f last.o5m

    echo "osm2pgsql"
    PGPASSWORD=$PSQL_PASSWORD /root/compilati/osm2pgsql-1.3.0/build/osm2pgsql -d osm2cai -U osm2cai -H localhost  -l -C 3000 -j -r o5m --output=flex --drop -S hiking_flex.lua -s hiking.o5m
    rm hiking.o5m

    echo "OSM2CAI SYNC"
    cd $LARAVEL_PATH
    /usr/bin/php artisan osm2cai:sync 

    echo "osmblock finished"
}

function siteup () {
    logit "osm2cai: setting osm2cai down during SYNC"
    logit "LARAVEL_PATH: $LARAVEL_PATH"

    cd $LARAVEL_PATH
    /usr/bin/php artisan up
}

function logit() {
    echo ""
    echo "==============="
    date
    echo "$1"
    echo "==============="
    echo ""
}


# MAIN
logit "osm2cai: start"

sitedown
dbbackup

#osmblock https://download.geofabrik.de/europe/italy/nord-ovest-latest.osm.pbf
#osmblock https://download.geofabrik.de/europe/italy/nord-est-latest.osm.pbf
osmblock https://download.geofabrik.de/europe/italy/isole-latest.osm.pbf
#osmblock https://download.geofabrik.de/europe/italy/centro-latest.osm.pbf
#osmblock https://download.geofabrik.de/europe/italy/sud-latest.osm.pbf

siteup

logit "osm2cai: end"
