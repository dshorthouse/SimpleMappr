#!/bin/sh

packagelist=(
  php5-cli
  php5-common
  php5-curl
  php5-dev
  php5-fpm
  php5-gd
  php5-imagick
  php5-mysql
)

sudo apt-get install ${packagelist[@]}