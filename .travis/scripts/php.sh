#!/bin/sh

sudo add-apt-repository ppa:ondrej/php -y
sudo apt-get update

sudo apt-get install -y \
php5.6 \
php5.6-cli \
php5.6-common \
php5.6-curl \
php5.6-dev \
php5.6-fpm \
php5.6-gd \
php5.6-imagick \
php5.6-mysql \
php-xdebug