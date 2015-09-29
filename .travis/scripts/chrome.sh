#!/bin/sh

echo "---> Starting Chrome driver installation"

wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | sudo apt-key add - 
sudo sh -c 'echo "deb http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list'

sudo apt-get update

sudo apt-get -y install google-chrome-stable

echo "---> Getting ChromeDriver and Selenium..."
wget "http://chromedriver.storage.googleapis.com/2.19/chromedriver_linux64.zip"
wget "http://selenium-release.storage.googleapis.com/2.47/selenium-server-standalone-2.47.1.jar"
unzip chromedriver_linux64.zip
sudo chmod 755 chromedriver
sudo mv chromedriver /usr/bin
sudo mv selenium-server-standalone-2.47.1.jar /usr/bin/selenium.jar

echo "---> Launching Selenium-Server-Standalone..."
xvfb-run --server-args='-screen 0, 1024x768x16' java -jar /usr/bin/selenium.jar -Dwebdriver.chrome.bin=/usr/bin/google-chrome-stable -Dwebdriver.chrome.driver=/usr/bin/chromedriver -port 4444 > /dev/null &