SimpleMappr Installation and Configuration
==========================================

SimpleMappr, [http://www.simplemappr](http://www.simplemappr.net) is a web-based application that produces publication-quality geographic maps. This source code is released under MIT license.

    Developer: David P. Shorthouse
    Email: davidpshorthouse@gmail.com

[![Build Status](https://secure.travis-ci.org/dshorthouse/SimpleMappr.png?branch=master)](http://travis-ci.org/dshorthouse/SimpleMappr)

Requirements
--------------------------
PHP5.5+, Apache2.2.24+, MySQL 5.5.27+, MapServer 6.4.1 & its dependencies, Composer

Configuration Instructions
--------------------------

1. Ensure /public/tmp, /public/javascript/cache, and /public/stylesheets/cache/ are readable & writable
2. Create a logger.log file in /log and make it writeable
3. Download map data from Natural Earth Data, [http://www.naturalearthdata.com/](http://www.naturalearthdata.com/)
4. Extract Natural Earth shapefiles to /mapserver/maps/
5. Use MapServer's included shptree utility to make *.qix index files (e.g. $ shptree 10m_admin_0_countries.shp) for better performance rendering shapefiles
6. Make contents of /mapserver/fonts readable & executable
7. Adjust /config/conf.db.sample.php and /config/conf.sample.php and remove ".sample". These set db connection and constants, respectively.
8. If you wish to use Janrain's OpenID authentication system, sign-up at [http://rpxnow.com](http://rpxnow.com) and replace the RPX_KEY in your /config/conf.db.php
9. The jQuery-based front-end assumes clean URLs and operates in a RESTful fashion. If served from Apache, use mod_rewrite as follows:

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
1. Install PHP5.5. See [https://github.com/homebrew/homebrew-php](https://github.com/homebrew/homebrew-php)
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

3. Download [http://mapserver.org/download.html](MapServer) 6.4.1 tarball, [http://download.osgeo.org/mapserver/mapserver-6.4.1.tar.gz](http://download.osgeo.org/mapserver/mapserver-6.4.1.tar.gz)
4. Extract and cd into folder
5. Execute from command line:

          $ mkdir build; cd build; cmake .. \
            -DWITH_KML=1 \
            -DWITH_PHP=1 \
            -DWITH_FCGI=0 \
            -DWITH_SVGCAIRO=1

          $ make && make install

6. Verify that mapserv is working

          $ mapserv -v

7. Add extension=php_mapscript.so to php.ini and restart web server

Database
--------

SimpleMappr uses MySQL and a sample schema is included in /db.

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

Install all necessary dependencies using [https://getcomposer.org/](composer) and update them as required.

    $ composer install
    $ composer update

Tests
-----

PHPUnit is used for unit tests and [Selenium](http://selenium-release.storage.googleapis.com/index.html?path=2.41/) and Facebook's [php-webdriver](https://github.com/facebook/php-webdriver) are used for integration tests. [Composer](https://getcomposer.org/) is used to include dependencies.

    $ java -jar selenium-server-standalone-2.41.0.jar
    $ ./vendor/bin/phpunit -c Tests/firefox.phpunit.xml --stderr

If you wish to use Chrome instead of FireFox, the Selenium Chromedriver can be found at [http://chromedriver.storage.googleapis.com/index.html](http://chromedriver.storage.googleapis.com/index.html):

    $ java -jar selenium-server-standalone-2.41.0.jar -Dwebdriver.chrome.driver=/usr/bin/chromedriver
    $ ./vendor/bin/phpunit -c Tests/chrome.phpunit.xml --stderr

Copyright
---------

    Copyright (c) 2010 David P. Shorthouse

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
