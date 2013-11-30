#!/bin/sh

echo "---> Applying $(tput bold ; tput setaf 2)apache2 configuration$(tput sgr0)"

sudo a2enmod actions rewrite expires headers deflate alias fastcgi