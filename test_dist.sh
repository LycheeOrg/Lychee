#!/usr/bin/env bash
set -e
set -o pipefail

make dist-clean
cd Lychee
cp ../.env.${SQL}.travis .env
php artisan key:generate
php artisan migrate
php artisan migrate:rollback
cd ..
