#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/3.3/selenium-server-standalone-3.3.1.jar"
sudo mv selenium-server-standalone-3.3.1.jar /usr/local/bin/selenium.jar

wget "https://chromedriver.storage.googleapis.com/2.29/chromedriver_linux64.zip"
sudo unzip chromedriver_linux64.zip
sudo chmod +x chromedriver
sudo mv chromedriver /usr/local/bin/
export PATH=$PATH:/usr/local/bin/chromedriver

#wget "https://github.com/mozilla/geckodriver/releases/download/v0.15.0/geckodriver-v0.15.0-linux64.tar.gz"
#sudo tar -xzf geckodriver-v0.15.0-linux64.tar.gz
#sudo chmod +x geckodriver
#sudo mv geckodriver /usr/local/bin/

echo "---> Launching Selenium-Server-Standalone..."

sudo xvfb-run --error-file="$TRAVIS_BUILD_DIR/xvfb-error.log" --server-args='-screen 0, 1024x768x16' java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -Dwebdriver.chrome.logfile=$TRAVIS_BUILD_DIR/chrome.log -jar /usr/local/bin/selenium.jar -port 4444 > /dev/null &

#sudo xvfb-run --error-file="$TRAVIS_BUILD_DIR/xvfb-error.log" --server-args="-screen 0, 1024x768x16" java -Dwebdriver.gecko.driver=/usr/local/bin/geckodriver -jar /usr/local/bin/selenium.jar -port 4444 > /dev/null &