#!/bin/sh

echo "---> Extract map files from Natural Earth"

mkdir -p mapserver/maps/10m_cultural/10m_cultural/
mkdir -p mapserver/maps/10m_physical/
mkdir -p mapserver/maps/wwf_terr_ecos/

unzip Tests/files/ne_10m_admin_0_map_units.zip -d mapserver/maps/10m_cultural/10m_cultural/
unzip Tests/files/ne_10m_admin_1_states_provinces.zip -d mapserver/maps/10m_cultural/10m_cultural/
unzip Tests/files/ne_10m_admin_1_states_provinces_lines.zip -d mapserver/maps/10m_cultural/10m_cultural/
unzip Tests/files/ne_10m_lakes.zip -d mapserver/maps/10m_physical/
unzip Tests/files/ne_10m_land.zip -d mapserver/maps/10m_physical/
unzip Tests/files/wwf_terr_ecos.zip -d mapserver/maps/wwf_terr_ecos/