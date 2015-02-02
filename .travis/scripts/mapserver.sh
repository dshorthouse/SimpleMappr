#!/bin/sh

echo "---> Starting MapServer 6.4.1 installation"

wget http://download.osgeo.org/mapserver/mapserver-6.4.1.tar.gz
tar -zxvf mapserver-6.4.1.tar.gz
cd mapserver-6.4.1
mkdir build
cd build
cmake -DWITH_KML=1 -DWITH_PHP=1 -DWITH_FCGI=0 ..
make
sudo make install
cd ../../
