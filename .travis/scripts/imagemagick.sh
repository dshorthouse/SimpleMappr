#!/bin/sh

imagemagick="ImageMagick-7.0.5-9"
imagick="imagick-3.4.3"

echo "---> Starting $(tput bold ; tput setaf 2)$imagemagick installation$(tput sgr0)"

wget https://www.imagemagick.org/download/$imagemagick.tar.gz
tar -zxvf $imagemagick.tar.gz
cd $imagemagick
./configure
make
sudo make install
sudo ldconfig /usr/local/lib
cd ../

echo "---> Starting $(tput bold ; tput setaf 2)$imagick installation$(tput sgr0)"

wget https://pecl.php.net/get/$imagick.tgz
tar -zxvf $imagick.tgz
cd $imagick
phpize
./configure
cd ../