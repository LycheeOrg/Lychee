#!/bin/sh
set -e

if [ $TRAVIS_COMPOSER_DEV = "yes" ]
then
  echo "vendor/bin/phpunit --verbose"
  vendor/bin/phpunit --verbose
else
  echo "vendor/bin/phpunit is not provided"
fi

