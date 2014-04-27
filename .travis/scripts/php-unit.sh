#!/bin/sh

echo "---> Updating PHPUnit"

sudo pear install --alldeps phpunit/PHPUnit
sudo pear install --force --alldeps phpunit/PHPUnit
phpunit --version