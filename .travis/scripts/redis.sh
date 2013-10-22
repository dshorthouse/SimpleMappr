#!/bin/sh

git clone git://github.com/nicolasff/phpredis.git
cd phpredis
phpize
./configure
make
sudo -s make install
cd ../