#!/bin/bash

# Variabili
SERVER_PROD="root@116.203.180.132"
DUMP_REMOTE_PATH="/root/osm2cai/storage/app/database/osm2cai/last-dump.sql.gz"
DUMP_LOCAL_PATH="/root/html/osm2cai/storage/app/database/last-dump.sql.gz" # Corretto il percorso
DB_NAME="osm2cai"
DB_USER="postgres"
DB_PASSWORD="9PDev#2sB&zDzIzL47yar4s"

# Scarica il dump dal server di produzione
scp $SERVER_PROD:$DUMP_REMOTE_PATH $DUMP_LOCAL_PATH

# Decomprimi il dump
gzip -d $DUMP_LOCAL_PATH


# Elimina il database esistente
psql -h 127.0.0.1 -U $DB_USER -c "DROP DATABASE osm2cai"

# Crea un nuovo database vuoto
psql -h 127.0.0.1 -U $DB_USER -c "CREATE DATABASE $DB_NAME"

# Importa il dump nel nuovo database
psql -h 127.0.0.1 -U $DB_USER -d $DB_NAME -f "${DUMP_LOCAL_PATH%.gz}"

# Pulizia
rm "${DUMP_LOCAL_PATH%.gz}"
