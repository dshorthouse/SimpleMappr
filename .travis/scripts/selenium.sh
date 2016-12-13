#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/2.53/selenium-server-standalone-2.53.1.jar"
sudo mv selenium-server-standalone-2.53.1.jar /usr/bin/selenium.jar

echo "---> Launching Selenium-Server-Standalone..."
xvfb-run --server-args='-screen 0, 1024x768x16' java -jar /usr/bin/selenium.jar -port 4444 > /dev/null &