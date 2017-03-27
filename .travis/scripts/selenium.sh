#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/3.3/selenium-server-standalone-3.3.1.jar"
sudo mv selenium-server-standalone-3.3.1.jar /usr/bin/selenium.jar

wget "https://chromedriver.storage.googleapis.com/2.28/chromedriver_linux64.zip"
unzip chromedriver_linux64.zip
sudo mv chromedriver /usr/bin/chromedriver

echo "---> Launching Selenium-Server-Standalone..."
xvfb-run --server-args='-screen 0, 1024x768x16' java -Dwebdriver.chrome.driver=/usr/bin/chromedriver -jar /usr/bin/selenium.jar -port 4444 > /dev/null &