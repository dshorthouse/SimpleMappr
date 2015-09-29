#!/bin/sh

echo $FOO

EXTRA_PACKETS="
apache2 \
build-essential \
libapache2-mod-fastcgi \
php5 \
php5-cli \
php5-common \
php5-dev \
php5-gd \
php5-mysql \
php5-curl \
locales \
gettext \
autoconf \
libjpeg-dev \
libpng12-dev \
libagg-dev \
freetype* \
libgdal1-dev \
libgdal-dev \
libproj-dev \
libxml2-dev \
libgeos-dev \
libcairo2-dev \
libghc-svgcairo-dev \
libfribidi-dev \
phpunit \
imagemagick \
gtk2-engines-pixbuf \
libgtk2.0-0 \
xvfb \
unzip \
openjdk-7-jre \
cmake"

if [ "$1" ]
then
    EXTRA_PACKETS="$EXTRA_PACKETS $1"
fi

echo "---> Starting $(tput bold ; tput setaf 2)packets installation$(tput sgr0)"
echo "---> Packets to install : $(tput bold ; tput setaf 3)$EXTRA_PACKETS$(tput sgr0)"

sudo apt-get update
sudo apt-get install -y --force-yes $EXTRA_PACKETS
printf "\n" | pecl install imagick
