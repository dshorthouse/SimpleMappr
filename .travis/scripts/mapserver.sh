#!/bin/sh

echo $FOO

echo "---> Starting MapServer 6.4.0 installation"

wget http://download.osgeo.org/mapserver/mapserver-6.4.0.tar.gz
tar -zxvf mapserver-6.4.0.tar.gz
cmake mapserver-6.4.0 -DWITH_KML=1 -DWITH_PHP=1 -DWITH_FCGI=0
make
sudo make install
mapserv -v
sudo service apache2 restart
