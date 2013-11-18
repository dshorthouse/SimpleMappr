SimpleMappr Installation and Configuration Instructions
=======================================================

This is the code for [SimpleMappr](http://www.simplemappr.net), an application to produce publication-quality geographic maps.

    Developer: David P. Shorthouse
    Email: davidpshorthouse@gmail.com

[![Build Status](https://secure.travis-ci.org/dshorthouse/SimpleMappr.png?branch=master)](http://travis-ci.org/dshorthouse/SimpleMappr)

Requirements
--------------------------
PHP5.5+, Apache2.2.24+, MySQL 5.5.27+, MapServer 6.4.0 & its dependencies

Configuration Instructions
--------------------------

1. Ensure /public/tmp, /public/javascript/cache, and /public/stylesheets/cache/ are readable & writable
2. Create a logger.log file in /log and make it writeable
3. Download map data from Natural Earth Data, [http://www.naturalearthdata.com/](http://www.naturalearthdata.com/)
4. Extract Natural Earth shapefiles to /lib/mapserver/maps/
5. Use MapServer's included shptree utility to make *.qix index files (e.g. $ shptree 10m_admin_0_countries.shp) for better performance rendering shapefiles
6. Make contents of /lib/mapserver/fonts readable & executable
7. Adjust /config/conf.db.sample.php and /config/conf.sample.php and remove ".sample". These set db (MySQL) connections and constants, respectively.
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
1. Install PHP5.5. See [https://github.com/josegonzalez/homebrew-php](https://github.com/josegonzalez/homebrew-php)
2. Execute from command line:

        $ brew install \
          autoconf \
          freetype \
          gd --with-freetype \
          gdal \
          geos \
          gettext \
          icu4c \
          jpeg \
          libpng \
          proj \
          cairo \
          libsvg-cairo \
          fribidi

3. Download MapServer 6.4.0 tarball, http://mapserver.org/download.html (e.g. [http://download.osgeo.org/mapserver/mapserver-6.4.0.tar.gz](http://download.osgeo.org/mapserver/mapserver-6.4.0.tar.gz))
4. Extract and cd into folder
5. Execute from command line:

          $ cmake \
            -DWITH_KML=1 \
            -DWITH_PHP=1 \
            -DWITH_FCGI=0 \
            -DWITH_SVGCAIRO=1

6. Execute $ make && make install
7. Verify that mapserv is working $ mapserv -v
8. Add extension=php_mapscript.so to php.ini and restart web server [You may also need to restart server]

Database
--------

SimpleMappr uses MySQL and a sample schema is included in /db.

Internationalization
--------------------

The following two commands make a messages.po file (by reading the index.php file) and a binary messages.mo file. Both need to be moved to relevant i18n directory such as i18n/fr\_FR.UTF-8/LC\_MESSAGES. You'll need to translate the strings in messages.po before making the binary of course.

    $ xgettext -n index.php
    $ msgfmt messages.po

Alternatively, you can use the ruby utility, crawler.rb from the /i18n directory to make a messages.po file and move it to i18n/fr\_FR.UTF-8/LC\_MESSAGES.

    $ cd i18n
    $ ruby crawler.rb ../

Tests
-----

Execute from the command line:

    $ phpunit --configuration Tests/phpunit.xml --stderr

Copyright
---------

    Copyright (c) David P. Shorthouse
    License: MIT (see included LICENSE file) and comments in each class in /lib and js file in /public/javascript
