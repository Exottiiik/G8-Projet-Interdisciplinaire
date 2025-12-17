#!/bin/bash

# ==== CONFIG =======

KEEP_DAYS=14
SRC_DIR="/etc/apache2/ /etc/mysql/"
DEST_DIR="/backup/config/"
SNAPSHOT_FILE="/backup/config/backup_config.snar"
FULL_BACKUP_DAY=6 # (1 = lundi, 7 = dimanche)

DAY_OF_WEEK=`date +%u`
DATESTAMP=`date +%F-%H-%M-%S`

# Crée un fichier snar si il n'existe pas sinon -> incrémental

if [ $DAY_OF_WEEK = $FULL_BACKUP_DAY ]; then
echo "Taking Full Backup"
rm -f SNAPSHOT_FILE
FILENAME="backup-$DATESTAMP-full.tar.gz"
tar -czvf $DEST_DIR/$FILENAME $SRC_DIR
else
echo "Taking Incremental Backup"

FILENAME="backup-$DATESTAMP-inc.tar.gz"
tar -czvf $DEST_DIR/$FILENAME -g $SNAPSHOT_FILE $SRC_DIR
fi

# rotation des backups
find $DEST_DIR -type f -name '*.tar.gz' -mtime +${KEEP_DAYS} -exec rm {} \;