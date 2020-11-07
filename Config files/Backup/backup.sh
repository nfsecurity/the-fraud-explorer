#!/bin/bash

# The Fraud Explorer
# https://www.thefraudexplorer.com/
#
# Copyright (c) 2014-2020 The Fraud Explorer
# email: customer@thefraudexplorer.com
# Licensed under GNU GPL v3
# https://www.thefraudexplorer.com/License
#
# Author: jrios@nofraud.la
# Version code-name: nemesis
#
# Description: Backup procedure

TIME=`date +"%b-%d-%y"`
FILENAME="backup-data-${TIME}.tar.gz"
TFEDIR="/var/www/html/thefraudexplorer"
ANALYTICSDIR="/var/lib/elasticsearch /etc/elasticsearch /etc/logstash"
SYSFILES="/etc/sysconfig /etc/my.cnf /etc/httpd /etc/php.ini /etc/inittab /backup/bin/backup.sh /etc/motd /etc/postfix /etc/hosts"
DESDIR="/backup"
ZIP_PASSWORD="mypass"
DB_TFE_PASSWORD="Nhb1&lS&"

# Verify if there is free space to do the backup

function SPACEUSED()
{
 USED="df -hT /backup | grep dev | awk '{ print \$6 }'"
 PERCENTUSED=$(eval "$USED")
 INTUSEDCOMMAND="echo $PERCENTUSED | cut -d\"%\" -f1"
 INTUSED=$(eval "$INTUSEDCOMMAND")
 
 echo $INTUSED
}

SPACEOCCUPED="$(SPACEUSED)"

while [ $SPACEOCCUPED -gt 65 ]
do
 
 OLDESTCOMMAND="ls /backup/*.zip -t | tail -1" 
 OLDESTBACKUP=$(eval "$OLDESTCOMMAND")

 if [ -z $OLDESTBACKUP ]; then
  break
 fi

 rm -f $OLDESTBACKUP

 SPACEOCCUPED="$(SPACEUSED)"

done

# Now we can do the backup

tar -cpzf ${DESDIR}/$FILENAME $TFEDIR $ANALYTICSDIR
tar -cpzf ${DESDIR}/backup-operating-system-configs-${TIME}.tar.gz $SYSFILES
mysqldump -u tfe -p${DB_TFE_PASSWORD} thefraudexplorer > /backup/thefraudexplorer-${TIME}.sql
tar -cpzf /backup/all-backup-${TIME}.tar.gz /backup/${FILENAME} /backup/backup-operating-system-configs-${TIME}.tar.gz /backup/thefraudexplorer-${TIME}.sql
zip --password $ZIP_PASSWORD /backup/backup-${TIME}.zip /backup/all-backup-${TIME}.tar.gz
rm -f /backup/all-backup-${TIME}.tar.gz /backup/backup-data-${TIME}.tar.gz /backup/backup-operating-system-configs-${TIME}.tar.gz /backup/thefraudexplorer-${TIME}.sql
