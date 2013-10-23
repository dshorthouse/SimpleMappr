#!/bin/sh

pear channel-discover pear.phpunit.de
pear install phpunit/PHP_Invoker
pear install phpunit/DbUnit
pear install phpunit/PHPUnit_Selenium
pear install phpunit/PHPUnit_Story

wget http://selenium.googlecode.com/files/selenium-server-standalone-2.9.0.jar
java -jar selenium-server-standalone-2.9.0.jar > /dev/null 2>&1 &
sleep 30