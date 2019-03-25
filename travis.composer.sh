#!/bin/sh
set -e

if [ $TRAVIS_COMPOSER_DEV = "yes" ]
then
  echo "composer self-update"
  composer self-update
  echo "composer install --no-interaction"
  composer install --no-interaction
else
  echo "composer self-update"
  composer self-update
  echo "composer install --no-interaction --no-dev"
  composer install --no-interaction --no-dev
fi

