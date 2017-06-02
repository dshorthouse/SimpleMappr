#!/bin/sh

imagemagick="ImageMagick-7.0.5-9"
imagick="imagick-3.4.3"

echo "---> Starting $imagemagick installation"

wget https://www.imagemagick.org/download/$imagemagick.tar.gz
tar -zxvf $imagemagick.tar.gz
cd $imagemagick
./configure
make
sudo make install
sudo ldconfig /usr/local/lib
cd ../

echo "---> Starting $imagick installation"

wget https://pecl.php.net/get/$imagick.tgz
tar -zxvf $imagick.tgz
cd $imagick
phpize
./configure
sudo checkinstall
sudo php5enmod imagick
cd ../