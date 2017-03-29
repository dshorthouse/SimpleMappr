#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/3.3/selenium-server-standalone-3.3.1.jar"
sudo mv selenium-server-standalone-3.3.1.jar /usr/local/bin/selenium.jar

#wget "https://github.com/mozilla/geckodriver/releases/download/v0.15.0/geckodriver-v0.15.0-linux64.tar.gz"
#sudo tar -xzf geckodriver-v0.15.0-linux64.tar.gz
#sudo mv geckodriver /usr/local/bin/
#sudo chmod +x /usr/local/bin/geckodriver

wget "https://chromedriver.storage.googleapis.com/2.28/chromedriver_linux64.zip"
unzip chromedriver_linux64.zip
sudo chmod +x chromedriver
sudo mv chromedriver /usr/local/bin

echo "---> Launching Selenium-Server-Standalone..."

#sudo xvfb-run --server-args='-screen 0, 1024x768x16' java -Dwebdriver.gecko.driver=/usr/local/bin/geckodriver -jar /usr/local/bin/selenium.jar -port 4444 > /dev/null &

sudo xvfb-run --server-args='-screen 0, 1024x768x16' java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -jar /usr/local/bin/selenium.jar -port 4444 > /dev/null &