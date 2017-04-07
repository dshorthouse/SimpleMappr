#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/3.3/selenium-server-standalone-3.3.1.jar"
sudo mv selenium-server-standalone-3.3.1.jar /usr/local/bin/selenium.jar

wget "https://chromedriver.storage.googleapis.com/2.29/chromedriver_linux64.zip"
sudo unzip chromedriver_linux64.zip
sudo chmod +x chromedriver
sudo mv chromedriver /usr/local/bin/chromedriver
export PATH=$PATH:/usr/local/bin/chromedriver
export CHROME_BIN=/usr/bin/google-chrome

echo "---> Launching Selenium-Server-Standalone..."

#sudo xvfb-run --error-file="$TRAVIS_BUILD_DIR/xvfb-error.log" --server-args="-screen 0 1024x768x24" java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -Dwebdriver.chrome.logfile=$TRAVIS_BUILD_DIR/chrome.log -jar /usr/local/bin/selenium.jar -port 4444 -log $TRAVIS_BUILD_DIR/selenium.log > /dev/null &

java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -Dwebdriver.chrome.logfile=$TRAVIS_BUILD_DIR/chrome.log -jar /usr/local/bin/selenium.jar > /dev/null &