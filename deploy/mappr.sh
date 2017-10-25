cd /var/www/SimpleMappr
echo "Flushing local caches..."
rm -rf public/javascript/cache/*.js
rm -rf public/stylesheets/cache/*.css
rm -rf public/tmp/*
echo "Stashing local changes..."
git stash
echo "Pulling code..."
git pull
echo "Updating composer packages..."
composer update -o
echo "Executing migrations..."
./vendor/bin/phinx migrate -c config/phinx.yml -e production
echo "Rebuilding French translations..."
cd /var/www/SimpleMappr/i18n/fr_FR.UTF-8/LC_MESSAGES
rm -rf messages.mo
msgfmt messages.po
echo "Rebuilding local caches..."
wget -q -O /dev/null "http://www.simplemappr.net"
echo "Flushing CloudFlare..."
php ~/deploy/cloudflare_flush.php
echo "Complete"
echo "Restarting Apache..."
service apache2 restart
