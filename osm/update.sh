#!/bin/bash

# INSTRUCTION
# 1. Copy update.sh and hiking_flex.lua files into working directory (outside laravel root dir)
# 2. Set proper parameters in update.sh file
# 3. run script
# 4. Eventually put it in crontab

# PARAMETERS
# PATH=/mnt/caiosm-data
# PBF=https://download.geofabrik.de/europe/italy/isole-latest.osm.pbf
# PBF=https://download.geofabrik.de/europe/italy-latest.osm.pbf
# PASSWORD=XXX
# PGCOMMAND="pgsql -d osm2cai"
# PGCOMMAND="/root/compilati/osm2pgsql-1.3.0/build/osm2pgsql -d settori -U caiadmin -H localhost"
# LUA=/root/hiking_flex.lua
# LUA=hiking_flex.lua

# cd $PATH

# START SCRIPT

wget --no-check-certificate -c -O last.pbf https://download.geofabrik.de/europe/italy/isole-latest.osm.pbf
echo "running osmconvert..."
osmconvert last.pbf > last.o5m
rm -f last.pbf
echo "running osmfilter..."
osmfilter last.o5m --keep-relations=route=hiking --hash-memory=3000 -o=hiking.o5m
rm -f last.o5m
# PGPASSWORD=XXXX /root/compilati/osm2pgsql-1.3.0/build/osm2pgsql -d settori -U caiadmin -H localhost  -l -C 3000 -j -r o5m --output=flex --drop -S $LUA -s hiking.o5m
osm2pgsql -d osm2cai -l -C 3000 -j -r o5m --output=flex --drop -S hiking_flex.lua -s hiking.o5m
rm -rf  hiking.o5m
