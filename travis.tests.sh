#!/bin/sh
set -ev

if [ $TRAVIS_COMPOSER_DEV = "yes" ]
then
  vendor/bin/phpunit --verbose
else
  echo "vendor/bin/phpunit is not provided"
fi

