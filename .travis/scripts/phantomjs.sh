#!/bin/sh

echo "---> Starting PhantomJS installation"

git clone git://github.com/ariya/phantomjs.git
cd phantomjs
git checkout 2.0
./build.sh
sudo mv bin/phantomjs /usr/bin/
cd ../