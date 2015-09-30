#!/bin/sh

echo "---> Applying $(tput bold ; tput setaf 2)apache2 configuration$(tput sgr0)"

sudo a2enmod actions rewrite expires headers deflate alias fastcgi
sudo touch /usr/lib/cgi-bin/php5.fcgi
sudo a2enconf php5-fpm
sudo service apache2 restart && sudo service php5-fpm restart