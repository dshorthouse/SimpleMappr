#!/bin/sh

echo $FOO

echo "---> Starting MapServer 6.4.0 installation"

wget http://download.osgeo.org/mapserver/mapserver-6.4.0.tar.gz
tar -zxvf mapserver-6.4.0.tar.gz
cmake mapserver-6.4.0 -DWITH_KML=1 -DWITH_PHP=1 -DWITH_FCGI=0
make
sudo make install

echo "---> Download map files from Natural Earth"
mkdir -p lib/mapserver/maps/10m_cultural/10m_cultural/
wget http://www.naturalearthdata.com/http//www.naturalearthdata.com/download/10m/cultural/ne_10m_admin_0_map_units.zip
unzip ne_10m_admin_0_map_units.zip -d lib/mapserver/maps/10m_cultural/10m_cultural/
