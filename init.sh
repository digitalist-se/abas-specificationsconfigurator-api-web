#!/usr/bin/env bash

composer install
if [ "$(expr substr $(uname -s) 1 10)" == "MINGW64_NT" ]; then
    vendor\\bin\\homestead make
else
    php vendor/bin/homestead make
fi

# copy homestead config from example
cp -n Homestead.yaml.example Homestead.yaml

echo "Homestead initialized!"

# copy app env from example
cp -n .env.example .env
