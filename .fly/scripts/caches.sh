#!/usr/bin/env bash

/usr/bin/php /var/www/html/artisan config:cache --no-ansi -q
/usr/bin/php /var/www/html/artisan route:cache --no-ansi -q
/usr/bin/php /var/www/html/artisan view:cache --no-ansi -q
/usr/bin/php /var/www/html/artisan migrate:fresh --force
/usr/bin/php /var/www/html/artisan lychee:create_user admin admin --may-administrate
/usr/bin/php /var/www/html/artisan lychee:create_user user password
/usr/bin/php /var/www/html/artisan lychee:sync /var/www/html/.fly/import/ --skip_duplicates=1
/usr/bin/php /var/www/html/artisan db:seed --class=DemoSeeder

