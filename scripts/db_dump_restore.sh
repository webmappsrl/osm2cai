#!/bin/bash

# Variabili
SERVER_PROD="root@116.203.180.132"
DUMP_REMOTE_PATH="/root/osm2cai/storage/app/database/osm2cai/last-dump.sql.gz"
DUMP_LOCAL_PATH="/root/html/osm2cai/storage/app/databaselast-dump.sql.gz"
DB_NAME="osm2cai"
DB_USER="postgres"
DB_PASSWORD="9PDev#2sB&zDzIzL47yar4s"

scp $SERVER_PROD:$DUMP_REMOTE_PATH $DUMP_LOCAL_PATH

gzip -d $DUMP_LOCAL_PATH

export PGPASSWORD=$DB_PASSWORD
psql -h localhost -U $DB_USER -d $DB_NAME -f "${DUMP_LOCAL_PATH%.gz}"

rm "${DUMP_LOCAL_PATH%.gz}"
