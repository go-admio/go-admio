#!/bin/bash

echo "$(date +"[%b %d %H:%M:%S]#") $(basename "$0") $@" | sudo tee -a "/var/log/go/$(basename "$0").log" >/dev/null

sudo rm -rf /root/.ssh/*

if sudo test -f /var/go/etc/go.key ; then

    MAC=$(sudo /usr/sbin/ifconfig | awk '/ether/ {print $2}' | base64)
    Password=$(sudo openssl base64 -d -aes-128-cbc -d -pbkdf2 -nosalt -md md5 -in /var/go/etc/go.key -pass pass:"$MAC")

else

    read -p "Password: " -e Password

fi

# sudo /go/language

sudo rm -rf /root/blackhost-almalinux8-kvm-gpt.sh

if [[ "$@" = *"install"* ]]; then

    if sudo test -d "/etc/apache2" ; then
        # Ubuntu
        sudo apt -y install shc
    else

        # CentOS

        sudo yum -y install shc
        sudo yum -y install sshpass
        sudo yum -y groupinstall "Development Tools"

        # wget http://www.datsi.fi.upm.es/~frosal/sources/shc-3.8.9.tgz
        # tar xvfz shc-3.8.9.tgz
        # cd shc-3.8.9
        # make
        # mv shc /bin/shc
    fi

fi

# no compile
if [[ ! "$@" = *"--n"* ]]; then

    sudo mkdir -p /home/download.goadmio.com/www/bin
    sudo rm -rf /home/download.goadmio.com/www/bin/*
    sudo mkdir -p /home/download.goadmio.com/www/bin/install
    sudo mkdir -p /home/download.goadmio.com/www/bin/scripts
    # add this line to bash file:

    N=0

    cd /root
    for File in *.sh; do

        if [ "$File" != "compile.sh" ] && [ "$File" != "compile2.sh" ] && [[ "$File" != *" "* ]]; then

            # Output=${File/.sh}
            Output=$(echo "$File" | cut -d '.' -f1)

            N=$(($N + 1))

            echo "$N| Compiling $File"
            # shc -r -f $File
            CFLAGS="-static" shc -r -f $File -o /home/download.goadmio.com/www/bin/$Output

        fi

    done

    sudo rm -rf /home/download.goadmio.com/www/index.html
    sudo cp latest.sh /home/download.goadmio.com/www/index.html

    # do not compile this file , not working in background if compiled
    yes | cp -rf /root/logo.img /home/download.goadmio.com/www/bin/logo.img
    yes | cp -rf /root/function.sh /home/download.goadmio.com/www/bin/function
    yes | cp -rf /root/control.sh /home/download.goadmio.com/www/bin/control
    yes | cp -rf /root/change-log.txt /home/download.goadmio.com/www/bin/
    yes | cp -rf /root/README.md /home/download.goadmio.com/www/bin/
    yes | cp -rf /root/LICENSE.md /home/download.goadmio.com/www/bin/
    sudo chmod a+x,go-w /home/download.goadmio.com/www/bin/web
    sudo chmod a+x,go-w /home/download.goadmio.com/www/bin/function
    sudo chmod a+x,go-w /home/download.goadmio.com/www/bin/control
    sudo chmod a+x,go-w /home/download.goadmio.com/www/bin/fpm

fi

if [[ "$@" = *"stable"* ]]; then

    Version=$(</home/download.goadmio.com/www/home/go/update/latest.txt)
    Version=$(($Version + 1))
    echo "$Version" | sudo tee /home/download.goadmio.com/www/home/go/update/latest.txt >/dev/null

    VersionFile="stable.tar.gz"

else

    VersionFile="beta.tar.gz"

fi

echo "TAR Folders"
cd /home/download.goadmio.com/www
sudo rm -rf $VersionFile
sudo tar -cpzf $VersionFile home bin so includes install script shell wordpress

echo "Checking $VersionFile"
gunzip -t $VersionFile

echo "Copying index.html to mirror.goadmio.com"
    sudo sshpass -p "$Password" rsync -t -h --info=progress2 \
    -e "ssh -p 22 -o StrictHostKeyChecking=no -o LogLevel=QUIET" \
    "index.html" root@mirror.goadmio.com:/home/mirror.goadmio.com/www
       

echo "Copying $VersionFile to mirror.goadmio.com"
sudo sshpass -p "$Password" ssh -p 22 -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -q root@mirror.goadmio.com "curl -k -L --retry 2 --retry-delay 5 --connect-timeout 30 --speed-limit 1 --speed-time 30 --progress-bar -o /home/mirror.goadmio.com/www/stable.tar.gz https://download.goadmio.com/stable.tar.gz"

echo "___________________"

sudo rm -rf /root/*.sh.x.c

cd /home/goadmio.com/www/commands/md
for File in *; do

    File=$(echo "$File" | cut -d '.' -f1)

    if [ ! -f "/root/$File.sh" ] && [ "$File" != "Home" ]; then
        echo "Error: Upload /root/$File.sh or rm -f /home/goadmio.com/www/commands/md/$File.md && rm -f /home/goadmio.com/www/commands/md/$File.html"
    fi

done

cd /root
for File in *.sh; do

    File=$(echo "$File" | cut -d '.' -f1)

    if [ ! -f "/home/goadmio.com/www/commands/md/$File.md" ] && [ ! -f "/home/goadmio.com/www/commands/md/$File.html" ]; then   
        
        if [ "$M" != "1" ]; then
            M="1"
            echo "# Missing Markdown Or:"
        fi

        echo "echo 1 > /Users/infomls.eg/c/www/goadmio.com/commands/md/$File.html"
    
    fi

done
