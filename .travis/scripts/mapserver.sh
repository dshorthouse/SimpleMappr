#!/bin/sh

mapserver="mapserver-7.0.5"

echo "---> Starting $mapserver installation"

wget http://download.osgeo.org/mapserver/$mapserver.tar.gz
tar -zxvf $mapserver.tar.gz
mkdir $mapserver/build
cd $mapserver/build
cmake -DWITH_KML=1 -DWITH_PHP=1 -DWITH_FCGI=1 -DWITH_SVGCAIRO=0 -DWITH_HARFBUZZ=0 -DWITH_FRIBIDI=0 ..
make
sudo make install
cd ../../
mapserv -v
