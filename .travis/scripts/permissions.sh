#!/bin/sh

sudo chmod -R 775 *
sudo chmod -R 777 public/tmp/
sudo chmod -R 777 public/javascript/cache/
sudo chmod -R 777 public/stylesheets/cache/
sudo chmod -R 777 config/
sudo chmod -R 777 mapserver/
sudo chmod -R 777 log/
sudo chmod -R 666 /usr/lib/cgi-bin
sudo chown -R www-data:www-data /usr/lib/cgi-bin