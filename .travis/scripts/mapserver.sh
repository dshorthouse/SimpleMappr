#!/bin/sh

echo "---> Starting MapServer 7.0.0 installation"

wget http://download.osgeo.org/mapserver/mapserver-7.0.0.tar.gz
tar -zxvf mapserver-7.0.0.tar.gz
cd mapserver-7.0.0
mkdir build
cd build
cmake -DWITH_KML=1 -DWITH_PHP=1 -DWITH_FCGI=0 -DWITH_SVGCAIRO=0 -DWITH_HARFBUZZ=0 -DWITH_FRIBIDI=0 ..
make
sudo make install
cd ../../
