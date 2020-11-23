#!/usr/bin/env bash
set -e
set -o pipefail

composer install --prefer-dist --no-interaction
cp .env.${SQL}.travis .env
vendor/bin/php-cs-fixer fix --config=.php_cs --verbose --diff --dry-run
php artisan key:generate
php artisan migrate
vendor/bin/phpunit --verbose
php artisan migrate:rollback
rm -fr vendor