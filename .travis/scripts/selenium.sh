#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/3.3/selenium-server-standalone-3.3.1.jar"
sudo mv selenium-server-standalone-3.3.1.jar /usr/local/bin/selenium.jar

wget "https://github.com/mozilla/geckodriver/releases/download/v0.15.0/geckodriver-v0.15.0-linux64.tar.gz"
sudo tar -xzf geckodriver-v0.15.0-linux64.tar.gz
sudo mv geckodriver /usr/local/bin/
sudo chmod +x /usr/local/bin/geckodriver

echo "---> Launching Selenium-Server-Standalone..."
sudo xvfb-run --server-args='-screen 0, 1024x768x16' java -Dwebdriver.gecko.driver=/usr/local/bin/geckodriver -jar /usr/local/bin/selenium.jar -port 4444 > /dev/null &

echo "---> Test connection to Selenium..."
wget --retry-connrefused --tries=5 --waitretry=1 --output-file=/dev/null --output-document=/dev/null "http://localhost:4444/wd/hub/status"
if [ ! $? -eq 0 ]; then
    echo "$(tput bold ; tput setaf 1)Selenium NOT running$(tput sgr0)"
else
    echo "$(tput bold ; tput setaf 2)Selenium running$(tput sgr0)"
fi