#!/bin/bash

phpenv config-add .travis/travis.php.ini
phpenv rehash

search="MapScript"
mapscript="$(php -m | grep $search)"

if [ "$mapscript" = "$search" ]; then
   echo $(tput bold ; tput setaf 2)$search installed$(tput sgr0)
 else
   echo $(tput bold ; tput setaf 1)$search NOT installed$(tput sgr0)
fi