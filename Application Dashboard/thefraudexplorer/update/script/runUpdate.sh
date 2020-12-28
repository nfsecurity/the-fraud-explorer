#!/bin/bash

# The Fraud Explorer
# https://www.thefraudexplorer.com/
#
# Copyright (c) 2014-2021 The Fraud Explorer
# email: customer@thefraudexplorer.com
# Licensed under GNU GPL v3
# https://www.thefraudexplorer.com/License
#
# Author: jrios@nofraud.la
# Version code-name: nemesis
#
# Description: Code for software update

URL=$1
REPODIR="/var/www/html/thefraudexplorer/update/repodata/"
REPODATA="$REPODIR/the-fraud-explorer-master/Application Dashboard/thefraudexplorer/"
TFEDIRECTORY="/var/www/html/thefraudexplorer/"

# Download and unzip master file

if [ ! -d $REPODIR ]; then
  mkdir $REPODIR
fi

cd $REPODIR
/usr/bin/wget $URL

if [ $? -eq 0 ]; then
    
    /usr/bin/unzip $REPODIR/master.zip

    # Sync remote files with local files

    /usr/bin/rsync -aq --exclude config.ini --exclude update.xml --exclude 'core/rules/*.json' "$REPODATA" "$TFEDIRECTORY" 
    chown -R apache:apache /var/www/html/thefraudexplorer

    # Remove temporary directory and files

    rm -f $REPODIR/master.zip
    rm -rf $REPODIR/the-fraud-explorer-master/

else
    echo FAIL
fi