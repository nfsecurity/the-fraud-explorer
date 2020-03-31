#!/bin/bash

# The Fraud Explorer
# https://www.thefraudexplorer.com/
#
# Copyright (c) 2014-2020 The Fraud Explorer
# email: customer@thefraudexplorer.com
# Licensed under GNU GPL v3
# https://www.thefraudexplorer.com/License
#
# Date: 2020-02
# Revision: v1.4.2-aim
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

    /usr/bin/rsync -aq --exclude config.ini --exclude update.xml "$REPODATA" "$TFEDIRECTORY" 
    chown -R apache:apache /var/www/html/thefraudexplorer

    # Remove temporary directory and files

    rm -f $REPODIR/master.zip
    rm -rf $REPODIR/the-fraud-explorer-master/

else
    echo FAIL
fi
