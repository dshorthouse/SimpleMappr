Creation of translation files

Background:http://onlamp.com/pub/a/php/2002/06/13/php.html

Apache server requires php5_gettext.so extension for PHP5

$ xgettext -n index.php [Makes a message.po file to be moved to locale directories]

$ msgfmt messages.po [Makes a binary message.mo]

Or, use the ruby utility, crawler.rb:

$ sudo ruby crawler.rb ../