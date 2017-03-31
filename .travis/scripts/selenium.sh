#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/3.3/selenium-server-standalone-3.3.1.jar"
sudo mv selenium-server-standalone-3.3.1.jar /usr/local/bin/selenium.jar

wget "https://github.com/mozilla/geckodriver/releases/download/v0.15.0/geckodriver-v0.15.0-linux64.tar.gz"
sudo tar -xzf geckodriver-v0.15.0-linux64.tar.gz
sudo chmod +x geckodriver
sudo mv geckodriver /usr/local/bin/
export PATH=$PATH:/usr/local/bin/geckodriver

echo "---> Launching Selenium-Server-Standalone..."

sudo xvfb-run --server-args='-screen 0, 1024x768x16' java -Dwebdriver.gecko.driver=/usr/local/bin/geckodriver -jar /usr/local/bin/selenium.jar -port 4444 > /dev/null &