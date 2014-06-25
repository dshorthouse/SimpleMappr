#!/bin/sh

DBNAME="simplemappr_testing"
if [ "$1" ]
then
    DBNAME="$1"
fi

USERNAME="root"
if [ "$2" ]
then
    USERNAME="$2"
fi

PASSWORD=""
if [ "$3" ]
then
    PASSWORD="-p$3"
fi

HASPASSWORD="without password"
if [ "$PASSWORD" ]
then
    HASPASSWORD="with password"
fi

echo "---> Creating $(tput bold ; tput setaf 2)MySQL database$(tput sgr0) : $(tput bold ; tput setaf 3)$DBNAME$(tput sgr0)"
echo "---> User $(tput bold ; tput setaf 2)$USERNAME ($HASPASSWORD)$(tput sgr0)"

mysql -u$USERNAME $PASSWORD -e "DROP DATABASE IF EXISTS $DBNAME; CREATE DATABASE $DBNAME;"
