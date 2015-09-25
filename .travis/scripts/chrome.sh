#!/bin/sh

echo "---> Starting Chrome driver installation"

wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | sudo apt-key add - 
sudo sh -c 'echo "deb http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list'

sudo apt-get update

sudo apt-get -y install google-chrome-stable

echo "Starting Google Chrome ..."
google-chrome --remote-debugging-port=9222 &

sudo apt-get -y install xvfb gtk2-engines-pixbuf
sudo apt-get -y install xfonts-cyrillic xfonts-100dpi xfonts-75dpi xfonts-base xfonts-scalable

echo "---> Getting ChromeDriver and Selenium..."
wget "http://chromedriver.storage.googleapis.com/2.19/chromedriver_linux64.zip"
wget "http://selenium-release.storage.googleapis.com/2.47/selenium-server-standalone-2.47.1.jar"
unzip chromedriver_linux64.zip
mv chromedriver /usr/local/bin
mv selenium-server-standalone-2.47.1.jar /usr/local/bin/selenium.jar

echo "---> Launching Selenium-Server-Standalone..."
nohup java -jar ./selenium.jar -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver > /dev/null &