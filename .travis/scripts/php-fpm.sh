#!/bin/bash

# Setup PHP-FPM
echo "---> Configuring $(tput bold ; tput setaf 2)php-fpm$(tput sgr0)"

PHP_FPM_BIN="/home/travis/.phpenv/versions/$(phpenv version-name)/sbin/php-fpm"
PHP_FPM_CONF="/home/travis/.phpenv/versions/$(phpenv version-name)/etc/php-fpm.conf"
PHP_FPM_SOCK="/var/run/php-fpm.sock"
PHP_FPM_LOG="$TRAVIS_BUILD_DIR/php-fpm.log"

USER=$(whoami)
echo "php-fpm user = $(tput bold ; tput setaf 2)$USER$(tput sgr0)"

sudo touch "$PHP_FPM_LOG"

# Adjust php-fpm.ini
sed -i "s/@USER@/$USER/g" "$TRAVIS_BUILD_DIR/.travis/php-fpm.ini"
sed -i "s|@PHP_FPM_SOCK@|$PHP_FPM_SOCK|g" "$TRAVIS_BUILD_DIR/.travis/php-fpm.ini"
sed -i "s|@PHP_FPM_LOG@|$PHP_FPM_LOG|g" "$TRAVIS_BUILD_DIR/.travis/php-fpm.ini"
sed -i "s|@PATH@|$PATH|g" "$TRAVIS_BUILD_DIR/.travis/php-fpm.ini"

# Start daemon
echo "Starting php-fpm"
sudo $PHP_FPM_BIN --fpm-config "$TRAVIS_BUILD_DIR/.travis/php-fpm.ini"
sudo chown www-data:www-data /var/run/php-fpm.sock