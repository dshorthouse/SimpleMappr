#!/bin/sh

echo "---> Starting Chrome driver installation"
echo "deb http://dl.google.com/linux/chrome/deb/ stable main" | sudo tee -a /etc/apt/sources.list

wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | sudo apt-key add -
sudo apt-get -y install python python-pip
sudo pip install selenium

wget -c http://chromedriver.storage.googleapis.com/2.19/chromedriver_linux64.zip
unzip chromedriver_linux64.zip
sudo cp ./chromedriver /usr/bin/
sudo chmod ugo+rx /usr/bin/chromedriver

sudo apt-get -y install libxpm4 libxrender1 libgtk2.0-0 libnss3 libgconf-2-4
sudo apt-get -y install google-chrome-stable

sudo apt-get -y install xvfb gtk2-engines-pixbuf
sudo apt-get -y install xfonts-cyrillic xfonts-100dpi xfonts-75dpi xfonts-base xfonts-scalable

echo "Starting X virtual framebuffer (Xvfb) in background..."
Xvfb -ac :99 -screen 0 1280x1024x16 &
export DISPLAY=:99

echo "Starting Selenium in background..."
wget http://goo.gl/yLJLZg
java -jar selenium-server-standalone-2.47.1.jar -Dwebdriver.chrome.driver=/usr/bin/chromedriver > /dev/null &
sleep 5