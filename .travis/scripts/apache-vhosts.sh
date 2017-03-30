#!/bin/sh

echo "ServerName localhost" | sudo tee /etc/apache2/conf-available/fqdn.conf
sudo a2enconf fqdn

echo "---> Starting $(tput bold ; tput setaf 2)virtual host creation$(tput sgr0)"

sudo cp -f .travis/apache2/www_simplemappr_local.conf /etc/apache2/sites-available/www_simplemappr_local.conf
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/www_simplemappr_local.conf
sudo a2ensite www_simplemappr_local.conf

sudo cp -f .travis/apache2/img_simplemappr_local.conf /etc/apache2/sites-available/img_simplemappr_local.conf
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/img_simplemappr_local.conf
sudo a2ensite img_simplemappr_local.conf

sudo a2enmod actions rewrite expires headers

sudo touch /var/run/php-fpm.sock
sudo chmod 777 /var/run/php-fpm.sock