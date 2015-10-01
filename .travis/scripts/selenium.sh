#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/2.47/selenium-server-standalone-2.47.1.jar"
sudo mv selenium-server-standalone-2.47.1.jar /usr/bin/selenium.jar

wget https://s3.amazonaws.com/travis-phantomjs/phantomjs-2.0.0-ubuntu-12.04.tar.bz2
tar -xjf phantomjs-2.0.0-ubuntu-12.04.tar.bz2
sudo rm -rf /usr/local/phantomjs/bin/phantomjs
sudo mv phantomjs /usr/local/phantomjs/bin/phantomjs

echo "---> Launching Selenium-Server-Standalone..."
#xvfb-run --server-args='-screen 0, 1024x768x16' java -jar /usr/bin/selenium.jar  -Dphantomjs.binary.path=/usr/local/phantomjs/bin/phantomjs -port 4444 > /dev/null &
java -jar /usr/bin/selenium.jar  -Dphantomjs.binary.path=/usr/local/phantomjs/bin/phantomjs -port 4444 > /dev/null &