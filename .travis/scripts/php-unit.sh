#!/bin/sh

echo "---> Updating PHPUnit"

sudo pear channel-discover pear.phpunit.de
sudo pear update-channels
sudo pear upgrade-all
sudo pear install --alldeps phpunit/PHPUnit
phpenv rehash
phpunit --version