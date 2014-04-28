#!/bin/sh

echo "---> Download map files from Natural Earth"

mkdir -p lib/mapserver/maps/10m_cultural/10m_cultural/
mkdir -p lib/mapserver/maps/10m_physical/

wget http://www.naturalearthdata.com/http//www.naturalearthdata.com/download/10m/cultural/ne_10m_admin_0_map_units.zip
wget http://www.naturalearthdata.com/http//www.naturalearthdata.com/download/10m/cultural/ne_10m_admin_1_states_provinces.zip
wget http://www.naturalearthdata.com/http//www.naturalearthdata.com/download/10m/physical/ne_10m_lakes.zip

unzip ne_10m_admin_0_map_units.zip -d lib/mapserver/maps/10m_cultural/10m_cultural/
unzip ne_10m_admin_1_states_provinces.zip -d lib/mapserver/maps/10m_cultural/10m_cultural/
unzip ne_10m_lakes.zip -d lib/mapserver/maps/10m_physical/

sudo chmod -R 777 lib/mapserver/maps