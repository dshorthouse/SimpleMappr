#!/bin/sh

echo "---> Starting MapServer 7.0.3 installation"

tar -zxvf Tests/files/mapserver-7.0.3.tar.gz -C .
mkdir mapserver-7.0.3/build
cd mapserver-7.0.3/build
cmake -DWITH_KML=1 -DWITH_PHP=1 -DWITH_FCGI=1 -DWITH_SVGCAIRO=0 -DWITH_HARFBUZZ=0 -DWITH_FRIBIDI=0 ..
make
sudo make install
cd ../../
mapserv -v

search="MapScript"
mapscript="$(php -m | grep $search)"

if [ "$mapscript" = "$search" ]; then
   echo $(tput bold ; tput setaf 2)$search installed$(tput sgr0)
 else
   echo $(tput bold ; tput setaf 1)$search NOT installed$(tput sgr0)
fi
