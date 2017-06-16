#!/bin/sh

echo "---> Starting $(tput bold ; tput setaf 2)virtual host creation$(tput sgr0)"

sudo cp -f .travis/apache2/www_simplemappr_test.conf /etc/apache2/sites-available/www_simplemappr_test.conf
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/www_simplemappr_test.conf
sudo sed -e "s?%HOST%?$HOST?g" --in-place /etc/apache2/sites-available/www_simplemappr_test.conf
sudo a2ensite www_simplemappr_test.conf

sudo cp -f .travis/apache2/img_simplemappr_test.conf /etc/apache2/sites-available/img_simplemappr_test.conf
sudo sed -e "s?%TRAVIS_BUILD_DIR%?$(pwd)?g" --in-place /etc/apache2/sites-available/img_simplemappr_test.conf
sudo sed -e "s?%IMG_HOST%?$IMG_HOST?g" --in-place /etc/apache2/sites-available/www_simplemappr_test.conf
sudo a2ensite img_simplemappr_test.conf

sudo a2enmod actions alias fastcgi rewrite expires headers vhost_alias

sudo touch /var/run/php-fpm.sock
sudo chmod 777 /var/run/php-fpm.sock