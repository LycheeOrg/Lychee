#!/bin/sh

# Start PHP-FPM
php-fpm8.4 -D

# Start Nginx in the foreground
exec nginx