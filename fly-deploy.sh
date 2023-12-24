php /var/www/html/artisan migrate --force
php /var/www/html/artisan lychee:update_user admin admin || php /var/www/html/artisan lychee:create_user admin admin --may-administrate
php /var/www/html/artisan lychee:create_user user password