SimpleMappr Installation and Configuration
==========================================

SimpleMappr, [http://www.simplemappr.net](http://www.simplemappr.net) is a web-based application that produces publication-quality geographic maps. This source code is released under MIT license.

    Developer: David P. Shorthouse
    Email: davidpshorthouse@gmail.com

[![DOI](https://zenodo.org/badge/1777885.svg)](https://zenodo.org/badge/latestdoi/1777885)

Server Requirements
--------------------------

CI runs on GitHub Actions; see [.github/workflows/ci.yml](.github/workflows/ci.yml).

1. PHP 8.1+ [with cli, PDO, PDO-MySQL, GD, cURL, mbstring]
2. Apache 2.4+ [with rewrite] (or any equivalent web server)
3. MySQL 5.7+ / MariaDB 10.3+
4. [MapServer 7.x](http://www.mapserver.org/) [with PROJ, GDAL, GEOS, Cairo, PHP MapScript]
5. [Composer](https://getcomposer.org/) 2.x

Configuration Instructions
--------------------------

1. Download shapefiles from Natural Earth Data, [http://www.naturalearthdata.com/](http://www.naturalearthdata.com/) and extract into mapserver/maps/. Adjust Apache read permissions as necessary.
2. Rename and adjust:
  - [config/conf.php.sample](config/conf.php.sample) => config/conf.php
  - [config/phinx.yml.sample](config/phinx.yml.sample) => config/phinx.yml
  - [config/shapefiles.yml.sample](config/shapefiles.yml.sample) => config/shapefiles.yml
3. Adjust [config/conf.test.php](config/conf.test.php) used during execution of tests
4. **Authentication is not configured.** The original Janrain/RPXNOW OAuth broker shut down in 2020; the legacy hooks were removed during modernization. To restore sign-in, integrate an OAuth provider (e.g. via `league/oauth2-client`) and call `Session::writeSession()` with the resulting user record. See `src/Session.php` for the contract.
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

Homebrew on macOS
-------------------
1. Install a current PHP 8.x via `brew install php` (or `shivammathur/php` for pinned versions).
2. Install system libraries:

        $ brew install \
          autoconf \
          freetype \
          jpeg \
          libpng \
          gdal \
          geos \
          gettext \
          icu4c \
          proj \
          cairo \
          fribidi \
          composer

3. Build [MapServer](http://mapserver.org/download.html) 7.x with the PHP MapScript option enabled. Refer to upstream build instructions — MapServer 8.x dropped the bundled PHP MapScript extension, so 7.6.x is the recommended target for now. Add `extension=php_mapscript.so` to `php.ini` and restart your web server.

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

PHPUnit 9.x runs the unit suite. Browser-driven functional tests use [php-webdriver/webdriver](https://github.com/php-webdriver/php-webdriver) against a Selenium-compatible endpoint (e.g. Selenium 4 standalone, or a managed grid).

    $ docker run -d -p 4444:4444 selenium/standalone-chrome:latest
    $ BROWSER=chrome ./vendor/bin/phpunit -c Tests/phpunit.xml --stderr

Tests are split into suites entitled, "Unit", "Functional", "Binary", "Router"

    $ ./vendor/bin/phpunit -c Tests/phpunit.xml --testsuite "Unit" --stderr

JavaScript Minification
-----------------------

JavaScript files are minified using Google's [Closure Compiler](https://developers.google.com/closure/compiler/docs/gettingstarted_app) as follows:

    $ java -jar compiler.jar --js simplemappr.js --js_output_file simplemappr.min.js

Copyright
---------

    Copyright (c) 2010-2026 David P. Shorthouse

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
