#!/bin/bash

# update-release-date.sh

filePath="config/app.php"
newDate=$(date +%d/%m/%Y)
sed -i "s/'release_date' => \"[0-9]\{2\}\/[0-9]\{2\}\/[0-9]\{4\}\",/'release_date' => \"$newDate\",/g" $filePath