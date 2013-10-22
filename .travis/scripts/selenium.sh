#!/bin/sh

serverUrl='http://127.0.0.1:4444'
serverFile=selenium-server-standalone-2.9.0.jar
firefoxUrl=http://ftp.mozilla.org/pub/mozilla.org/firefox/releases/24.0/linux-x86_64/en-US/firefox-24.0.tar.bz2
firefoxFile=firefox.tar.bz2

pear channel-discover pear.phpunit.de
pear install phpunit/PHP_Invoker
pear install phpunit/DbUnit
pear install phpunit/PHPUnit_Selenium
pear install phpunit/PHPUnit_Story

echo "---> Downloading Firefox..."
wget $firefoxUrl -O $firefoxFile
tar xvjf $firefoxFile

echo "---> Starting xvfb..."
echo "---> Starting Selenium..."

if [ ! -f $serverFile ]; then
    wget http://selenium.googlecode.com/files/$serverFile
fi

sudo xvfb-run java -jar $serverFile > /tmp/selenium.log &
wget --retry-connrefused --tries=60 --waitretry=1 --output-file=/dev/null $serverUrl/wd/hub/status -O /dev/null

if [ ! $? -eq 0 ]; then
    echo "Selenium Server not started"
else
    echo "Finished setup"
fi