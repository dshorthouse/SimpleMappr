#!/bin/sh

BASEDIR=$(dirname $0)
BASEDIR=$(readlink -f "$BASEDIR/..")
ROOTDIR=$(readlink -f "$BASEDIR/..")

VHOSTNAME="www.simplemappr.local"
if [ "$1" ]
then
    VHOSTNAME="$1"
fi

DOCROOT="$ROOTDIR"
CONFIGFILE="$BASEDIR/apache2/$VHOSTNAME"

echo "---> Starting $(tput bold ; tput setaf 2)virtual host creation$(tput sgr0)"
echo "---> Virtualhost name : $(tput bold ; tput setaf 3)$VHOSTNAME$(tput sgr0)"
echo "---> Document root : $(tput bold ; tput setaf 3)$DOCROOT$(tput sgr0)"
echo "---> Configuration file : $(tput bold ; tput setaf 3)$CONFIGFILE$(tput sgr0)"

sed s?%basedir%?$DOCROOT? "$CONFIGFILE" | sed s/%hostname%/$VHOSTNAME/ > $VHOSTNAME
sudo mv $VHOSTNAME /etc/apache2/sites-available/$VHOSTNAME.conf

echo "---> Creating site $VHOSTNAME"
sudo a2ensite $VHOSTNAME
