#!/bin/sh

imagemagick="7.0.5-10"

echo "---> Starting $(tput bold ; tput setaf 2)$imagemagick installation$(tput sgr0)"

wget https://github.com/ImageMagick/ImageMagick/archive/$imagemagick.tar.gz
tar -zxvf $imagemagick.tar.gz
cd ImageMagick-$imagemagick
./configure
make
sudo make install
sudo ldconfig /usr/local/lib
cd ../

echo "---> Starting $(tput bold ; tput setaf 2)Imagick installation$(tput sgr0)"

printf "\n" | pecl install imagick-beta