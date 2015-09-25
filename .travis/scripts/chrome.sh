#!/bin/sh

echo "---> Starting Chrome driver installation"

wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | sudo apt-key add - 
sudo sh -c 'echo "deb http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list'

sudo apt-get update

sudo apt-get -y install libxpm4 libxrender1 libgtk2.0-0 libnss3 libgconf-2-4
sudo apt-get -y install google-chrome-stable

sudo apt-get -y install xvfb gtk2-engines-pixbuf
sudo apt-get -y install xfonts-cyrillic xfonts-100dpi xfonts-75dpi xfonts-base xfonts-scalable

echo "Starting Selenium in background..."
wget -c http://chromedriver.storage.googleapis.com/2.19/chromedriver_linux64.zip
unzip chromedriver_linux64.zip
sudo cp ./chromedriver /usr/bin/
sudo chmod ugo+rx /usr/bin/chromedriver

wget http://selenium-release.storage.googleapis.com/2.45/selenium-server-standalone-2.45.0.jar

java -jar selenium-server-standalone-2.45.0.jar -Dwebdriver.chrome.driver=/usr/bin/chromedriver > /dev/null &