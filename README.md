SimpleMappr Installation and Configuration
==========================================

SimpleMappr, [http://www.simplemappr.net](http://www.simplemappr.net) is a web-based application that produces publication-quality geographic maps. This source code is released under MIT license.

    Developer: David P. Shorthouse
    Email: davidpshorthouse@gmail.com

[![Build Status](https://secure.travis-ci.org/dshorthouse/SimpleMappr.png?branch=master)](http://travis-ci.org/dshorthouse/SimpleMappr)
[![Coverage Status](https://coveralls.io/repos/dshorthouse/SimpleMappr/badge.svg?branch=master&service=github)](https://coveralls.io/github/dshorthouse/SimpleMappr?branch=master)
[![Join the chat at https://gitter.im/dshorthouse/SimpleMappr](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/dshorthouse/SimpleMappr?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

Server Requirements
--------------------------

See how the [travis.yml](.travis.yml) file is configured for [Travis-CI](https://travis-ci.org/)'s continuous integration of automated unit and functional testing.

1. PHP5.6+ [with cli, PDO, PDO-MySQL, GD]
2. Apache2.2.24+ [with rewrite]
3. MySQL 5.5.27+
4. [MapServer 7.0.5](http://www.mapserver.org/) [with PROJ, GDAL, GEOS, Cairo]
5. [Composer](https://getcomposer.org/)

Configuration Instructions
--------------------------

1. Download shapefiles from Natural Earth Data, [http://www.naturalearthdata.com/](http://www.naturalearthdata.com/) and extract into mapserver/maps/. Adjust Apache read permissions as necessary.
2. Rename and adjust:
  - [config/conf.php.sample](config/conf.php.sample) => config/conf.php
  - [config/phinx.yml.sample](config/phinx.yml.sample) => config/phinx.yml
  - [config/shapefiles.yml.sample](config/shapefiles.yml.sample) => config/shapefiles.yml
3. Adjust [config/conf.test.php](config/conf.test.php) used during execution of tests
4. If you wish to use Janrain's OpenID authentication system, sign-up at [http://rpxnow.com](http://rpxnow.com) and replace the RPX_KEY in config/conf.php
5. The jQuery-based front-end assumes clean URLs and operates in a RESTful fashion. Configure mod_rewrite as follows:

### Apache Rewrite Configuration

    <VirtualHost *:80>
      ServerName mydomain.net
      ServerAlias mydomain.net
      DocumentRoot /path/to/your/root
      <Directory "/path/to/your/root">
       Options -Indexes +FollowSymlinks
       AllowOverride None
       Order allow,deny
       Allow from all
       DirectoryIndex index.php
       RewriteEngine on
       RewriteBase /
       RewriteRule ^(public|sitemap.xml|robots.txt)($|/) - [L]
       RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-f
       RewriteCond %{DOCUMENT_ROOT}%{REQUEST_FILENAME} !-d
       RewriteRule ^(.*)$ index.php?q=$1 [L,QSA]
      </Directory>
    </VirtualHost>

Homebrew on Mac OSX
-------------------
1. Install PHP5.6. See [https://github.com/homebrew/homebrew-php](https://github.com/homebrew/homebrew-php)
2. Execute from command line:

        $ brew install \
          autoconf \
          freetype \
          jpeg \
          libpng \
          gd --with-freetype --with-png --with-jpeg --with-tiff \
          gdal \
          geos \
          gettext \
          icu4c \
          proj \
          cairo \
          libsvg-cairo \
          fribidi \
          phpunit \
          composer

3. Download [MapServer](http://mapserver.org/download.html) 7.0.6 tarball, [http://download.osgeo.org/mapserver/mapserver-7.0.6.tar.gz](http://download.osgeo.org/mapserver/mapserver-7.0.6.tar.gz)
4. Extract and cd into folder
5. Execute from command line:

          $ mkdir build; cd build; cmake .. \
            -DCMAKE_PREFIX_PATH="/usr/local/opt/libiconv;/usr/local/opt/php56;/usr/local/opt/phpunit;/usr/local/opt/geos;/usr/local/opt/pkg-config;/usr/local/opt/libxml2;/usr/local/opt/zlib;/usr/local/opt/fontconfig;/usr/local/opt/cairo;/usr/local/opt/fribidi;/usr/local/opt/proj;/usr/local/opt/giflib;/usr/local/opt/freetype;/usr/local/opt/libsvg;/usr/local/opt/libsvg-cairo;/usr/local/opt/gdal2;/usr/local/opt/libpng;/usr/local/opt/libjpeg;/usr/local/opt/harfbuzz" \
            -DWITH_KML=1 \
            -DWITH_PHP=1 \
            -DWITH_SVGCAIRO=1 \
            -DWITH_FCGI=0 \
            -DWITH_RSVG=0 \
            -DWITH_POSTGIS=0 \
            -DWITH_EXEMPI=0 \
            -DFRIBIDI_INCLUDE_DIR="/usr/local/include/glib-2.0;/usr/local/lib/glib-2.0/include;/usr/local/include/fribidi" \
            -DPHP5_INCLUDE_PATH="/usr/local/include/php"

          $ make && make install

6. Verify that mapserv is working

          $ mapserv -v

7. Add extension=php_mapscript.so to php.ini (if not already there) and restart web server

Unix-based Server
------------------

See the useful guide on [MapServer](http://mapserver.org/installation/unix.html).

Internationalization
--------------------

The following two commands make a messages.po file (by reading the index.php file) then a binary messages.mo file from a messages.po file as input. Both need to be moved to relevant i18n directory such as i18n/fr\_FR.UTF-8/LC\_MESSAGES. You'll need to translate the strings in messages.po before making the binary of course. Whenever any string is changed in any messages.po file, the messages.mo file must be generated and Apache must be restarted because translated strings are enumerated into memory when the application first loads.

    $ xgettext -n index.php
    $ msgfmt messages.po

Alternatively, you can use the ruby utility, crawler.rb from the /i18n directory to make a messages.po file and move it to i18n/fr\_FR.UTF-8/LC\_MESSAGES.

    $ cd i18n
    $ ruby crawler.rb ../views

Dependencies
------------

Install all necessary application dependencies using [composer](https://getcomposer.org) and update them as required.

    $ composer install
    $ composer update

Database
--------

SimpleMappr uses MySQL and [phinx](http://docs.phinx.org) for migrations. A sample schema is included in /db and migrations are stored in /db/migrations.
Create MySQL databases simplemappr, simplemappr\_development and simplemappr\_testing. Use /db/sample.db.sql to create tables.

    $ ./vendor/bin/phinx migrate -c config/phinx.yml -e development

Tests
-----

PHPUnit is used for unit tests and [Selenium](http://selenium-release.storage.googleapis.com/index.html?path=3.4/) and Facebook's [php-webdriver](https://github.com/facebook/php-webdriver) are used for integration tests. [Composer](https://getcomposer.org/) is used to include dependencies. The Firefox Gecko driver can be downloaded [here](https://github.com/mozilla/geckodriver/releases).

    $ java -Dwebdriver.gecko.driver=/usr/local/bin/geckodriver -jar /usr/local/bin/selenium-server-standalone-3.6.0.jar
    $ BROWSER=firefox ./vendor/bin/phpunit -c Tests/phpunit.xml --stderr

If you wish to use Chrome instead of FireFox, the Selenium Chromedriver can be found at [http://chromedriver.storage.googleapis.com/index.html](http://chromedriver.storage.googleapis.com/index.html):

    $ java -Dwebdriver.chrome.driver=/usr/local/bin/chromedriver -jar /usr/local/bin/selenium-server-standalone-3.6.0.jar
    $ BROWSER=chrome ./vendor/bin/phpunit -c Tests/phpunit.xml --stderr

Tests are split into suites entitled, "Unit", "Functional", "Binary", "Router"

    $ ./vendor/bin/phpunit -c Tests/phpunit.xml --testsuite "Unit" --stderr

JavaScript Minification
-----------------------

JavaScript files are minified using Google's [Closure Compiler](https://developers.google.com/closure/compiler/docs/gettingstarted_app) as follows:

    $ java -jar compiler.jar --js simplemappr.js --js_output_file simplemappr.min.js

Copyright
---------

    Copyright (c) 2010-2017 David P. Shorthouse

    Released under MIT License

    Permission is hereby granted, free of charge, to any person obtaining
    a copy of this software and associated documentation files (the
    "Software"), to deal in the Software without restriction, including
    without limitation the rights to use, copy, modify, merge, publish,
    distribute, sublicense, and/or sell copies of the Software, and to
    permit persons to whom the Software is furnished to do so, subject to
    the following conditions:

    The above copyright notice and this permission notice shall be
    included in all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
    EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
    NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
    LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
    OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
    WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
