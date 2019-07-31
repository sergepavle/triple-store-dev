#!/bin/bash
set -m

echo "Waiting for Virtuoso to be ready on 1111..."
if [ nc -z localhost 1111 ] ;
then
    echo "Virtuoso ready, importing data..."
    ./vendor/bin/robo fetch
    ./vendor/bin/robo purge
    ./vendor/bin/robo import
fi
