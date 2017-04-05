#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/3.3/selenium-server-standalone-3.3.1.jar"
sudo mv selenium-server-standalone-3.3.1.jar /usr/local/bin/selenium.jar

wget "https://chromedriver.storage.googleapis.com/2.29/chromedriver_linux64.zip"
sudo unzip chromedriver_linux64.zip
sudo chmod +x chromedriver
sudo mv chromedriver /usr/local/bin/
export PATH=$PATH:/usr/local/bin/chromedriver

echo "---> Launching Selenium-Server-Standalone..."

sudo xvfb-run --server-args='-screen 0, 1024x768x16' java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -Dwebdriver.chrome.logfile=$TRAVIS_BUILD_DIR/chrome.log -jar /usr/local/bin/selenium.jar -port 4444 > /dev/null &