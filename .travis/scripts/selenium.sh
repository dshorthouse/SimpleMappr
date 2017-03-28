#!/bin/sh

wget "http://selenium-release.storage.googleapis.com/3.3/selenium-server-standalone-3.3.1.jar"
sudo mv selenium-server-standalone-3.3.1.jar /usr/bin/selenium.jar

wget "https://github.com/mozilla/geckodriver/releases/download/v0.15.0/geckodriver-v0.15.0-linux64.tar.gz"
tar -xvzf geckodriver-v0.15.0-linux64.tar.gz -C /usr/local/bin/
sudo chmod +x /usr/local/bin/geckodriver
geckodriver -V

echo "---> Launching Selenium-Server-Standalone..."
xvfb-run --server-args='-screen 0, 1024x768x16' java -jar /usr/bin/selenium.jar -port 4444 > /dev/null &