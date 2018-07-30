#!/bin/sh

mapserver="mapserver-7.2.0"

echo "---> Starting $(tput bold ; tput setaf 2)Starting $mapserver installation$(tput sgr0)"

wget http://download.osgeo.org/mapserver/$mapserver.tar.gz
tar -zxvf $mapserver.tar.gz
mkdir $mapserver/build
cd $mapserver/build
cmake -DWITH_KML=1 -DWITH_PHP=1 -DWITH_FCGI=1 -DWITH_SVGCAIRO=0 -DWITH_HARFBUZZ=0 -DWITH_FRIBIDI=0 -DWITH_PROTOBUFC=0 ..
make
sudo make install
cd ../../
mapserv -v
