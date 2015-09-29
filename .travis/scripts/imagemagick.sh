#!/bin/sh

echo "---> Starting ImageMagick installation"

wget http://www.imagemagick.org/download/ImageMagick.tar.gz
tar -zxvf ImageMagick.tar.gz
cd ImageMagick-6.9.2-3
./configure
sudo make
sudo make install

wget https://pecl.php.net/get/imagick-3.1.2.tgz
tar -zxvf imagick-3.1.2.tgz
cd imagick-3.1.2
phpize && ./configure
sudo make
sudo make install
