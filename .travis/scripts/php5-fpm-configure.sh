#!/bin/sh

echo "---> Applying $(tput bold ; tput setaf 2)php-fpm configuration$(tput sgr0)"
cat /etc/php5/fpm/pool.d/www.conf