#!/usr/bin/env bash

/usr/bin/php /var/www/html/artisan config:cache --no-ansi -q
/usr/bin/php /var/www/html/artisan route:cache --no-ansi -q
/usr/bin/php /var/www/html/artisan view:cache --no-ansi -q
/usr/bin/php /var/www/html/artisan migrate:fresh --force
/usr/bin/php /var/www/html/artisan lychee:create_user admin admin --may-administrate
/usr/bin/php /var/www/html/artisan lychee:create_user user password
