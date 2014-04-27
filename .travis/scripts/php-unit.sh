#!/bin/sh

echo "---> Updating PHPUnit"

sudo pear channel-discover pear.phpunit.de
sudo pear install --force --alldeps phpunit/PHPUnit
phpunit --version