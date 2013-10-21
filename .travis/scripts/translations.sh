#!/bin/sh

BASEDIR=$(dirname $0)
BASEDIR=$(readlink -f "$BASEDIR/..")
ROOTDIR=$(readlink -f "$BASEDIR/..")

DOCROOT="$ROOTDIR"
if [ "$1" ]
then
    DOCROOT="$1"
fi

echo "---> Generating $(tput bold ; tput setaf 2)locales$(tput sgr0)"
sudo locale-gen
sudo locale-gen en_US.UTF-8
sudo locale-gen fr_FR.UTF-8

echo "---> Translating $(tput bold ; tput setaf 3)$DOCROOT$/i18n/fr_FR.UTF-8/LC_MESSAGES/messages.po(tput sgr0)"
msgfmt $DOCROOT/i18n/fr_FR.UTF-8/LC_MESSAGES/messages.po