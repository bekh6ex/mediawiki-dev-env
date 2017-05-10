#!/usr/bin/env bash
set -ex

service ssh start &

cd /var/www/html

if [ ! -f composer.local.json ]; then
    echo '{ "extra": { "merge-plugin": { "include": [ "extensions/*/composer.json" ] } } }' > composer.local.json
fi

composer update

set +e
# May fail if wiki is not installed yet
php maintenance/update.php --quick
set -e

apache2-foreground
