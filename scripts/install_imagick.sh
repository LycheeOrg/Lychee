#!/usr/bin/env bash
set -e

mkdir -p .github/build
git clone https://github.com/Imagick/imagick.git .github/build/imagick
cd .github/build/imagick && phpize && ./configure && make
DEST=$(php -i | grep 'extension_dir => /')
php -i | grep 'extension_dir => /'
php -i | grep 'extension_dir'
echo $DEST
DEST2=$(echo "${DEST##* }")
echo 'Copying imagick.so to ' $DEST2
sudo cp modules/imagick.so $DEST2
echo 'Update php.ini file at ' $(echo $(php --ini | grep 'Loaded Configuration File') | awk 'NF>1{print $NF}')
sudo echo 'extension="imagick.so"' >> $(echo $(php --ini | grep 'Loaded Configuration File') | awk 'NF>1{print $NF}')