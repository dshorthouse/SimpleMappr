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

echo "---> Download map files from Natural Earth"

mkdir -p lib/mapserver/maps/10m_cultural/10m_cultural/
mkdir -p lib/mapserver/maps/10m_physical/

wget http://www.naturalearthdata.com/http//www.naturalearthdata.com/download/10m/cultural/ne_10m_admin_0_map_units.zip
wget http://www.naturalearthdata.com/http//www.naturalearthdata.com/download/10m/cultural/ne_10m_admin_1_states_provinces.zip
wget http://www.naturalearthdata.com/http//www.naturalearthdata.com/download/10m/physical/ne_10m_lakes.zip

unzip ne_10m_admin_0_map_units.zip -d lib/mapserver/maps/10m_cultural/10m_cultural/
unzip ne_10m_admin_1_states_provinces.zip -d lib/mapserver/maps/10m_cultural/10m_cultural/
unzip ne_10m_lakes.zip -d lib/mapserver/10m_physical/
