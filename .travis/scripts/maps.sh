#!/bin/sh

echo "---> Download map files from Natural Earth"

mkdir -p mapserver/maps/10m_cultural/10m_cultural/
mkdir -p mapserver/maps/10m_physical/

wget http://naciscdn.org/naturalearth/10m/cultural/ne_10m_admin_0_map_units.zip
wget http://naciscdn.org/naturalearth/10m/cultural/ne_10m_admin_1_states_provinces.zip
wget http://naciscdn.org/naturalearth/10m/cultural/ne_10m_admin_1_states_provinces_lines.zip
wget http://naciscdn.org/naturalearth/10m/physical/ne_10m_lakes.zip
wget http://naciscdn.org/naturalearth/10m/physical/ne_10m_land.zip

unzip ne_10m_admin_0_map_units.zip -d mapserver/maps/10m_cultural/10m_cultural/
unzip ne_10m_admin_1_states_provinces.zip -d mapserver/maps/10m_cultural/10m_cultural/
unzip ne_10m_admin_1_states_provinces_lines.zip -d mapserver/maps/10m_cultural/10m_cultural/
unzip ne_10m_lakes.zip -d mapserver/maps/10m_physical/
unzip ne_10m_land.zip -d mapserver/maps/10m_physical/