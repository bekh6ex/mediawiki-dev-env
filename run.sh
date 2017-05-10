#!/usr/bin/env bash
set -ex

service ssh start &

cd /var/www/html

if [ ! -f composer.local.json ]; then
    echo '{ "extra": { "merge-plugin": { "include": [ "extensions/*/composer.json" ] } } }' > composer.local.json
fi

composer update

apache2-foreground
