#!/bin/sh

echo "---> Applying $(tput bold ; tput setaf 2)apache2 configuration$(tput sgr0)"

sudo a2enmod rewrite
sudo a2enmod expires
sudo a2ennod actions