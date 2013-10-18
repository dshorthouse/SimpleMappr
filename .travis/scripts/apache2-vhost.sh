#!/bin/sh

BASEDIR=$(dirname $0)
BASEDIR=$(readlink -f "$BASEDIR/..")
ROOTDIR=$(readlink -f "$BASEDIR/..")

VHOSTNAME="www.simplemappr.local"
if [ "$1" ]
then
    VHOSTNAME="$1"
fi

ALIAS="simplemappr.local"
if [ "$2" ]
then
    ALIAS="$2"
fi

DOCROOT="$ROOTDIR"
if [ "$3" ]
then
    DOCROOT="$3"
fi

CONFIGFILE="$BASEDIR/apache2/$VHOSTNAME"
if [ "$4" ]
then
    CONFIGFILE="$4"
fi

echo "---> Starting $(tput bold ; tput setaf 2)virtual host creation$(tput sgr0)"
echo "---> Virtualhost name : $(tput bold ; tput setaf 3)$VHOSTNAME$(tput sgr0)"
echo "---> Alias name: $(tput bold ; tput setaf 3)$ALIAS$(tput sgr0)"
echo "---> Document root : $(tput bold ; tput setaf 3)$DOCROOT$(tput sgr0)"
echo "---> Configuration file : $(tput bold ; tput setaf 3)$CONFIGFILE$(tput sgr0)"

sed s?%basedir%?$DOCROOT? "$CONFIGFILE" | sed s/%hostname%/$VHOSTNAME/ | sed s/%alias%/$ALIAS/ > $VHOSTNAME
sudo mv $VHOSTNAME /etc/apache2/sites-available/$VHOSTNAME

echo "---> $(tput bold ; tput setaf 2)Adding host to /etc/hosts$(tput sgr0) :"
echo "127.0.0.1    $VHOSTNAME" | sudo tee -a /etc/hosts
