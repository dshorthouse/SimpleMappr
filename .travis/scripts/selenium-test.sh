#!/bin/sh

echo "---> Test connection to Selenium..."

wget --retry-connrefused --tries=5 --waitretry=1 --output-file=/dev/null --output-document=/dev/null "http://localhost:4444/wd/hub/status"
if [ ! $? -eq 0 ]; then
    echo "$(tput bold ; tput setaf 1)Selenium NOT running$(tput sgr0)"
else
    echo "$(tput bold ; tput setaf 2)Selenium running$(tput sgr0)"
fi