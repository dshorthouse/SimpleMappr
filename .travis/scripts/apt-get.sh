#!/bin/sh

echo $FOO

EXTRA_PACKETS="
apache2 \
build-essential \
libapache2-mod-fastcgi \
php5-dev \
php5-mysql \
php5-curl \
locales \
gettext \
autoconf \
libjpeg-dev \
libpng-dev \
freetype* \
libgdal-dev \
libproj-dev \
libxml2-dev \
libgeos-dev \
libcairo2-dev \
libghc-svgcairo-dev \
libfribidi-dev \
phpunit \
php-pear \
gtk2-engines-pixbuf \
libgtk2.0-0 \
xvfb \
cmake"

if [ "$1" ]
then
    EXTRA_PACKETS="$EXTRA_PACKETS $1"
fi

echo "---> Starting $(tput bold ; tput setaf 2)packets installation$(tput sgr0)"
echo "---> Packets to install : $(tput bold ; tput setaf 3)$EXTRA_PACKETS$(tput sgr0)"

sudo apt-get update
sudo apt-get install -y --force-yes $EXTRA_PACKETS
