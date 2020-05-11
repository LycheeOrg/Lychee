#!/usr/bin/env bash

make dist-clean
cd Lychee
cp ../.env.${SQL}.travis .env
php artisan key:generate
php artisan migrate
php artisan migrate:rollback
