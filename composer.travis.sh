#!/bin/sh

echo $(TRAVIS_COMPOSER_DEV);

if [ $TRAVIS_COMPOSER_DEV = "yes" ]
then
  composer self-update
  composer install --no-interaction
else
  composer self-update
  composer install --no-interaction --no-dev
fi