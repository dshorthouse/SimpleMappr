#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/3.6/selenium-server-standalone-3.6.0.jar"
sudo mv selenium-server-standalone-3.6.0.jar /usr/local/bin/selenium.jar

wget "https://chromedriver.storage.googleapis.com/2.33/chromedriver_linux64.zip"
sudo unzip chromedriver_linux64.zip
sudo mv chromedriver /usr/local/bin/chromedriver
sudo chmod 777 /usr/local/bin/chromedriver
export PATH=$PATH:/usr/local/bin/chromedriver
export CHROME_BIN=/usr/bin/google-chrome

echo "---> Launching $(tput bold ; tput setaf 2)Selenium-Server-Standalone...$(tput sgr0)"

java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -jar /usr/local/bin/selenium.jar > /dev/null 2>/dev/null &