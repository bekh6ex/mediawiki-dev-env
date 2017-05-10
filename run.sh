#!/usr/bin/env bash
set -ex

service ssh start &

cd /var/www/html

if [ ! -f composer.local.json ]; then
    cp composer.local.json-sample composer.local.json
fi

apache2-foreground
