#!/bin/bash

if [ -z "$1" ] || [ -z "$2" ]; then
    echo 'Usage: path domain.com'
    exit
fi

path=$1
domain=$2

echo -e "+--------------------------------------------------------------+"
echo -e "|             Welcome to the Celibero setup.                   |"
echo -e "+--------------------------------------------------------------+"
echo -en "| Would you like to proceed with setup now? [Y/n]:"

read -e ANSWER

if [ "$ANSWER" = n ]
then
    echo -e "+--------------------------------------------------------------+"
    echo -e "| Exiting...                                                   |"
    echo -e "+--------------------------------------------------------------+"
    exit 0
fi

umask 022

echo -e "+--------------------------------------------------------------+"
echo -en "| Do you need the qmail compile patches (above RH8)? [Y/n]:"

read -e PATCH

echo -e "+--------------------------------------------------------------+"
echo -e "|                   Qmail Setup.                               |"
echo -e "+--------------------------------------------------------------+"
echo -en "| Do you need to install qmail? [Y/n]:"

read -e GO

if [ $GO = 'Y' ]; then

    echo -e "----------------------------------------------------------------"
    echo -en "| Copying the qmail tarball (will overwrite) ... "

    mkdir -p /usr/local/src
    cp --reply=yes $path/no-web/packages/netqmail-1.05.tar.gz /usr/local/src
    cd /usr/local/src
    echo -e "done"
    echo -e "----------------------------------------------------------------"

    echo -en "| Unpacking qmail tarball (will overwrite) ... "

    gunzip netqmail-1.05.tar.gz
    tar xpf netqmail-1.05.tar
    cd netqmail-1.05
    
    echo -e "done"
    echo -e "----------------------------------------------------------------"

    echo -e "| Running net qmail script ... "

    ./collate.sh
	
    echo -e "----------------------------------------------------------------"
    echo -e "| Making qmail dir and user/groups ... "

    mkdir -p /var/qmail

    /usr/sbin/groupadd nofiles
    /usr/sbin/useradd -g nofiles -d /var/qmail/alias alias
    /usr/sbin/useradd -g nofiles -d /var/qmail qmaild
    /usr/sbin/useradd -g nofiles -d /var/qmail qmaill
    /usr/sbin/useradd -g nofiles -d /var/qmail qmailp
    /usr/sbin/groupadd qmail
    /usr/sbin/useradd -g qmail -d /var/qmail qmailq
    /usr/sbin/useradd -g qmail -d /var/qmail qmailr
    /usr/sbin/useradd -g qmail -d /var/qmail qmails
    
    echo -e "| done"
    echo -e "----------------------------------------------------------------"
	
    echo -en "| Ready to build qmail? hit any key:"

    read -e GO
	
    cd /usr/local/src/netqmail-1.05/netqmail-1.05
    make setup check
    
    echo -e "| done"
    echo -e "----------------------------------------------------------------"

    echo -e "| Quick configure qmail ..                                     |"

    ./config-fast $domain
    
    echo '#' > /var/qmail/alias/.qmail-default
    echo '#' > /var/qmail/alias/.qmail-postmaster
    echo '#' > /var/qmail/alias/.qmail-return
    echo '#' > /var/qmail/alias/.qmail-mailer-daemon
    echo '#' > /var/qmail/alias/.qmail-root

    echo -e "| done                                                         |"
    echo -e "----------------------------------------------------------------"

fi

echo -e "+--------------------------------------------------------------+"
echo -e "|                   UCSPI-TCP Setup                            |"
echo -e "+--------------------------------------------------------------+"
echo -en "| Do you need to install ucspi-tcp? [Y/n]:"

read -e GO

if [ $GO = 'Y' ]; then

    echo -e "----------------------------------------------------------------"
    echo -en "| Copying the tarball (will overwrite) ... "
	
    mkdir -p /usr/local/src
    cp --reply=yes $path/no-web/packages/ucspi-tcp-0.88.tar.gz /usr/local/src
    cd /usr/local/src
    echo -e "done"
    echo -e "----------------------------------------------------------------"

    echo -en "| Unpacking tarball (will overwrite) ... "
	
    gunzip ucspi-tcp-0.88.tar.gz
    tar xpf ucspi-tcp-0.88.tar
    cd /usr/local/src/ucspi-tcp-0.88
    patch < /usr/local/src/netqmail-1.05/other-patches/ucspi-tcp-0.88.errno.patch
    
    echo -en "| Ready to build ucspi-tcp? hit any key:"

    read -e GO
	
    make
    make setup check

    echo -e "| done"
    echo -e "----------------------------------------------------------------"

fi

echo -e "+--------------------------------------------------------------+"
echo -e "|                   Daemontools Setup                          |"
echo -e "+--------------------------------------------------------------+"
echo -en "| Do you need to install daemontools? [Y/n]:"

read -e GO

if [ $GO = 'Y' ]; then

    echo -e "----------------------------------------------------------------"
    echo -en "| Copying the tarball (will overwrite) ... "
	
    mkdir -p /package
    chmod 1755 /package
    cp --reply=yes $path/no-web/packages/daemontools-0.76.tar.gz /package

    echo -e "done"
    echo -e "----------------------------------------------------------------"

    echo -en "| Unpacking tarball (will overwrite) ... "
	
    cd /package
    
    gunzip daemontools-0.76.tar.gz
    tar xpf daemontools-0.76.tar
    
    cd /package/admin/daemontools-0.76
    
    cd src
    patch < /usr/local/src/netqmail-1.05/other-patches/daemontools-0.76.errno.patch
    cd ..
    
    echo -en "| Ready to build daemontools? hit any key:"

    read -e GO
	
    package/install

    echo -e "| done"
    echo -e "----------------------------------------------------------------"

fi

echo -e "+--------------------------------------------------------------+"
echo -e "|                   Running Scripts                            |"
echo -e "+--------------------------------------------------------------+"
echo -en "| Do you need to install run scripts? [Y/n]:"

read -e GO

if [ $GO = 'Y' ]; then
    
    echo -e "| Qmail Control Script...."
    echo -e "----------------------------------------------------------------"
	
    cp --reply=yes $path/no-web/packages/scripts/qmailctl /var/qmail/bin/qmailctl
    chmod 755 /var/qmail/bin/qmailctl
    ln -s /var/qmail/bin/qmailctl /usr/bin
    
    cp --reply=yes $path/no-web/packages/scripts/qmail-rc /var/qmail/rc
    chmod 755 /var/qmail/rc
    mkdir /var/log/qmail
    
    cd ~alias; touch .qmail-postmaster .qmail-mailer-daemon .qmail-root
    ln -sf /var/qmail/bin/sendmail /usr/lib/sendmail
    ln -sf /var/qmail/bin/sendmail /usr/sbin/sendmail
    
    echo ./Mailbox >/var/qmail/control/defaultdelivery
    
    echo -e "| done"
    
    echo -e "----------------------------------------------------------------"
    echo -e "| Supervise directories...."
    echo -e "----------------------------------------------------------------"
	
    mkdir -p /var/qmail/supervise/qmail-send/log
    chown root -R /var/qmail/supervise/qmail-send/
	
    mkdir -p /var/qmail/supervise/qmail-smtpd/log
    chown root -R /var/qmail/supervise/qmail-smtpd/
    
    mkdir -p /var/qmail/supervise/qmail-smtpd/log/main
    chown qmaill /var/qmail/supervise/qmail-smtpd/log/main
    
    mkdir -p /var/qmail/supervise/qmail-send/log/main
    chown qmaill /var/qmail/supervise/qmail-send/log/main
    
    cp --reply=yes $path/no-web/packages/scripts/qmail-send-run /var/qmail/supervise/qmail-send/run
    cp --reply=yes $path/no-web/packages/scripts/log-run /var/qmail/supervise/qmail-send/log/run
  
    chmod +x /var/qmail/supervise/qmail-send/run
    chmod +x /var/qmail/supervise/qmail-send/log/run
    
    cp --reply=yes $path/no-web/packages/scripts/qmail-smtpd-run /var/qmail/supervise/qmail-smtpd/run
    cp --reply=yes $path/no-web/packages/scripts/log-run /var/qmail/supervise/qmail-smtpd/log/run
  
    chmod +x /var/qmail/supervise/qmail-smtpd/run
    chmod +x /var/qmail/supervise/qmail-smtpd/log/run
    
    echo -e "----------------------------------------------------------------"
    echo -e "| Linking scripts                                              |"
    echo -e "----------------------------------------------------------------"
    
    ln -s /var/qmail/supervise/qmail-smtpd /service
    ln -s /var/qmail/supervise/qmail-send /service
    
    echo -e "| SVS Stat wait 10 secs                                        |"
    echo -e "----------------------------------------------------------------"
	
    echo -en "| Ready? hit any key:"

    read -e GO
    
    svstat /service/qmail-smtpd /service/qmail-smtpd/log
    svstat /service/qmail-send /service/qmail-send/log
fi

echo -e "+--------------------------------------------------------------+"
echo -e "|                     Crontabs                                 |"
echo -e "+--------------------------------------------------------------+"

echo -en "| Do you need to install crontabs? [Y/n]:"

read -e GO

if [ $GO = 'Y' ]; then

  echo -e "\n\n"
  echo "* * * * * "$path"/no-web/crons/minute-control.php >/dev/null 2>&1"
  echo "01 * * * * "$path"/no-web/crons/hour-control.php >/dev/null 2>&1"
  echo "* 23 * * * "$path"/no-web/crons/day-control.php >/dev/null 2>&1"
 
  echo -e "\n\n"
  echo "Press any key to launch cron tab when in press a then paste above then press esc then ZZ:"
  read -e NONE
  crontab -e
  echo "\n"$path"/no-web/crons/startup.php >> /etc/rc.local"
  echo -e "| Done                                                         |"
  echo -e "----------------------------------------------------------------"

fi


echo -e "+--------------------------------------------------------------+"
echo -e "|                     Server Prep                              |"
echo -e "+--------------------------------------------------------------+"

echo -en "| Do you need to prep? [Y/n]:"

read -e GO

if [ $GO = 'Y' ]; then
  
    echo -e "----------------------------------------------------------------"
    echo -e "| Adding user upload                                           |"
    echo -e "----------------------------------------------------------------"
	
    /usr/sbin/useradd upload
    passwd upload
    
    mkdir /home/upload/export
    mkdir /home/upload/import
  
    chmod -R 777 /home/upload/
    
    echo -e "| Qmail Handle is always good                                  |"
    echo -e "----------------------------------------------------------------"
	
    cd $path/no-web/packages/
    ln -s $path/img ~upload/img
    tar -zxf qmhandle-1.2.0.tar.gz
    rm -rdf GPL HISTORY README
    mv -f qmHandle /var/qmail/bin/
	
fi

echo -e "+--------------------------------------------------------------+"
echo -e "|                     Celiberod                                |"
echo -e "+--------------------------------------------------------------+"

echo -en "| Do you need celiberod? [Y/n]:"

read -e GO

if [ $GO = 'Y' ]; then
  
    echo -e "----------------------------------------------------------------"
    echo -e "| Adding user celibero                                          |"
    echo -e "----------------------------------------------------------------"

    /usr/sbin/groupadd celibero
    /usr/sbin/adduser -M -g celibero celibero
    /usr/sbin/usermod -d ""$path"/no-web/celiberod" celibero
    chown celibero ""$path"/no-web/celiberod/logs"
    
    chmod +x ""$path"/no-web/celiberod/bin/celiberodctl"
    chmod +x ""$path"/no-web/celiberod/bin/keepup"
    chmod +x ""$path"/no-web/celiberod/bin/bouncerd.php"
    chmod +x ""$path"/no-web/celiberod/bin/complaint.php"
    chmod 777 ""$path"/no-web/celiberod/body"
    chmod 777 ""$path"/no-web/celiberod/list"
    chmod 777 ""$path"/cp/scheduling/test"
    chmod 777 ""$path"/img"
    chmod +x -R ""$path"/no-web/crons/"
    
    chmod 777 ""$path"/no-web/templates/abuse.php"
    chmod 777 ""$path"/no-web/templates/index.php"
    chmod 777 ""$path"/no-web/templates/privacy-policy.php"
    chmod 777 ""$path"/no-web/templates/can-spam.php"
    
    chmod o=-rwx ""$path"/install/shell/pass"
    chown root ""$path"/install/shell/pass"
    
    echo "|"$path"/no-web/celiberod/bin/bouncer" > /var/qmail/alias/.qmail-default
    echo "|"$path"/no-web/celiberod/bin/bouncer" > /var/qmail/alias/.qmail-postmaster
    echo "|"$path"/no-web/celiberod/bin/bouncer" > /var/qmail/alias/.qmail-return
    echo "|"$path"/no-web/celiberod/bin/bouncer" > /var/qmail/alias/.qmail-mailer-daemon
    echo "|"$path"/no-web/celiberod/bin/bouncer" > /var/qmail/alias/.qmail-root
    echo "|"$path"/no-web/celiberod/bin/complaint.php" > /var/qmail/alias/.qmail-complaint
    
    /usr/sbin/adduser return
    /var/qmail/bin/maildirmake /home/return/Maildir
    chown -R return:return /home/return/Maildir
    passwd return
    #echo "/home/return/Maildir/" > /home/return/.qmail
    echo "|/www/celibero/no-web/celiberod/bin/bouncer" >> /home/return/.qmail
    
    chmod o=-w /home/return/.qmail
    chmod +r /home/return/.qmail

	echo -e "Adding gdne..."
	gunzip -c ""$path"/install/install-files/sgdne.sql.gz" > ""$path"/install/install-files/sgdne.sql"
	mysql -uroot -pcheese celibero < ""$path"/install/install-files/sgdne.sql"

    echo -en "| Ready to build celibero? hit any key:"

    read -e GO
	
    echo -e "| done"
    echo -e "----------------------------------------------------------------"

    	
fi
/usr/bin/php -q ""$path"/install/upgrade/upgrade-3.php"
/sbin/service crond restart

echo -en "| Update sgdne 3 times, hit any key:"

read -e GO
    
cd $path/no-web/crons/
./hour-control.php
./hour-control.php
./hour-control.php


echo -e "+--------------------------------------------------------------+"
echo -e "|                     DNS Cache                                |"
echo -e "+--------------------------------------------------------------+"

echo -en "| Do you need dnscache? [Y/n]:"

read -e GO

if [ $GO = 'Y' ]; then

    cd ~
    echo "dnscache:*:54321:54321:dnscache:/dev/null:/dev/null" >> /etc/passwd
    echo "dnslog:*:54322:54322:dnslog:/dev/null:/dev/null" >> /etc/passwd
    
    wget -t1 http://cr.yp.to/djbdns/djbdns-1.05.tar.gz
    tar zxvf djbdns-1.05.tar.gz
    cd djbdns-1.05
    echo gcc -O2 -include /usr/include/errno.h > conf-cc
    make
    make setup check
    cd /usr/local/bin
    ./dnscache-conf dnscache dnslog /etc/dnscache 0.0.0.0
    ln -s /etc/dnscache /service

fi

chown -R upload /www/celibero
echo -e "\n+--------------------------------------------------------------+"
echo -e "| Installation complete access at: http://www.$domain/cp/      |"
echo -e "+--------------------------------------------------------------+"
