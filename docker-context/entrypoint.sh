#!/bin/bash

set -e

# Read Last commit hash from .git
# This prevents installing git, and allows display of commit
branch=$(ls /var/www/html/Lychee/.git/refs/heads)
read -r longhash < /var/www/html/Lychee/.git/refs/heads/$branch
shorthash=$(echo $longhash |cut -c1-7)
lycheeversion=$(</var/www/html/Lychee/version.md)

echo '
-------------------------------------
  _               _                
 | |   _   _  ___| |__   ___  ___  
 | |  | | | |/ __|  _ \ / _ \/ _ \ 
 | |__| |_| | (__| | | |  __/  __/ 
 |_____\__, |\___|_| |_|\___|\___| 
       |___/                       

-------------------------------------
Lychee Version: '$lycheeversion' (local dev)
Lychee Branch:  '$branch'
Lychee Commit:  '$shorthash'
https://github.com/LycheeOrg/Lychee/commit/'$longhash'
-------------------------------------'

whoami

echo "**** Make sure the /conf /uploads /sym /logs /lychee-tmp folders exist ****"
[ ! -d /conf ]         && mkdir -p /conf
[ ! -d /uploads ]      && mkdir -p /uploads
[ ! -d /logs ]         && mkdir -p /logs
[ ! -d /lychee-tmp ]   && mkdir -p /lychee-tmp

echo "**** Create the symbolic link for the /uploads folder ****"
[ ! -L /var/www/html/Lychee/public/uploads ] && \
	cp -r /var/www/html/Lychee/public/uploads/* /uploads && \
	rm -r /var/www/html/Lychee/public/uploads && \
	ln -s /uploads /var/www/html/Lychee/public/uploads

echo "**** Create the symbolic link for the /logs folder ****"
[ ! -L /var/www/html/Lychee/storage/logs ] && \
	touch /var/www/html/Lychee/storage/logs/empty_file && \
	cp -r /var/www/html/Lychee/storage/logs/* /logs && \
	rm -r /var/www/html/Lychee/storage/logs && \
	ln -s /logs /var/www/html/Lychee/storage/logs

echo "**** Create the symbolic link for the /lychee-tmp folder ****"
[ ! -L /var/www/html/Lychee/storage/tmp ] && \
	touch /var/www/html/Lychee/storage/tmp/empty_file && \
	cp -r /var/www/html/Lychee/storage/tmp/* /lychee-tmp && \
	rm -r /var/www/html/Lychee/storage/tmp && \
	ln -s /lychee-tmp /var/www/html/Lychee/storage/tmp

cd /var/www/html/Lychee

if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DB_CONNECTION" ]
	then if [ -n "$DB_DATABASE" ]
		then if [ ! -e "$DB_DATABASE" ]
			then echo "**** Specified sqlite database doesn't exist. Creating it ****"
			echo "**** Please make sure your database is on a persistent volume ****"
			touch "$DB_DATABASE"
			chown www-data:www-data "$DB_DATABASE"
		fi
		chown www-data:www-data "$DB_DATABASE"
	else DB_DATABASE="/var/www/html/Lychee/database/database.sqlite"
		export DB_DATABASE
		if [ ! -L database/database.sqlite ]
			then [ ! -e /conf/database.sqlite ] && \
			echo "**** Copy the default database to /conf ****" && \
			cp database/database.sqlite /conf/database.sqlite
			echo "**** Create the symbolic link for the database ****"
			rm database/database.sqlite
			ln -s /conf/database.sqlite database/database.sqlite
			chown -h www-data:www-data /conf /conf/database.sqlite database/database.sqlite
		fi
	fi
fi

# echo "**** Copy the .env to /conf ****" && \
# [ ! -e /conf/.env ] && \
# 	sed 's|^#DB_DATABASE=$|DB_DATABASE='$DB_DATABASE'|' /var/www/html/Lychee/.env.example > /conf/.env
# [ ! -L /var/www/html/Lychee/.env ] && \
#   rm /var/www/html/Lychee/.env && \
# 	ln -s /conf/.env /var/www/html/Lychee/.env


[ ! -e /tmp/first_run ] && \
	echo "**** Generate the key (to make sure that cookies cannot be decrypted etc) ****" && \
	./artisan key:generate -n && \
	echo "**** Migrate the database ****" && \
	./artisan migrate --force && \
	touch /tmp/first_run


echo "**** Create user and use PUID/PGID ****"
PUID=${PUID:-1000}
PGID=${PGID:-1000}
if [ ! "$(id -u "$USER")" -eq "$PUID" ]; then usermod -o -u "$PUID" "$USER" ; fi
if [ ! "$(id -g "$USER")" -eq "$PGID" ]; then groupmod -o -g "$PGID" "$USER" ; fi
echo -e " \tUser UID :\t$(id -u "$USER")"
echo -e " \tUser GID :\t$(id -g "$USER")"
usermod -a -G "$USER" www-data

echo "**** Make sure Laravel's log exists ****" && \
touch /logs/laravel.log

echo "**** Setup npm run dev ****"

cd /var/www/html/Lychee && npm run dev &

echo "**** start php & nginx ****"


php-fpm8.4

exec $@
