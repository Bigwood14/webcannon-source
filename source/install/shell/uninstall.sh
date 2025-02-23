#!/bin/bash

if [ -z "$1" ]; then
    echo 'Usage: path mysql_user mysql_pass mysql_host mysql_db'
    exit
fi

$path = $1
$user = $2
$pass = $3
$host = $4
$db   = $5

echo -e "###########################################"
echo -e "Uninstall Celibero"
echo -e "###########################################"

echo -e "Kill blast? (y,n):"

if [ $GO = 'y' ]; then
    $path/scripts/blast.sh stop
    echo -e "Blast Killed"
fi

echo -e "Remove Celibero folder? (y,n):"

read -e GO

if [ $GO = 'y' ]; then
    rm -rdf $path
    echo -e "Folder Removed"
fi

echo -e "Remove Celibero binarys? (y,n):"

read -e GO

if [ $GO = 'y' ]; then
    rm -rdf /usr/bin/celibero/
    rm -rdf /etc/celibero.connect
    echo -e "Binarys Removed Removed"
fi

echo -e "Remove qmail? (y,n):"

read -e GO

if [ $GO = 'y' ]; then
    svc -td /service/qmail-send
    svc -td /service/qmail-smtpd
    svc -td /service/qmail-pop3d
    
    rm -rdf /var/qmail
    rm -rdf /usr/local/qmail
    echo -e "qmail Removed"
fi

echo -e "Remove tcp? (y,n):"

read -e GO

if [ $GO = 'y' ]; then
    svc -td /service/qmail-send
    svc -td /service/qmail-smtpd
    svc -td /service/qmail-pop3d
    
    rm -rdf /usr/local/tcp
    echo -e "tcp Removed"
fi

echo -e "Remove daemontools? (y,n):"

read -e GO

if [ $GO = 'y' ]; then
    svc -td /service/qmail-send
    svc -td /service/qmail-smtpd
    svc -td /service/qmail-pop3d
    
    rm -rdf /service
    rm -rdf /package
    echo -e "daemontools Removed"
fi

echo -e "Remove databases? (y,n):"

read -e GO

if [ $GO = 'y' ]; then
    echo -e "Section not completed"
fi

echo -e "###########################################"
echo -e "Uninstall Completed Goodbye ;-("
echo -e "###########################################"