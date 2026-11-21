#!/bin/bash
if [ "$EUID" -ne 0 ]; then
    exec sudo -- /go/password "$@"
fi

echo $(date +"[%b %d %H:%M:%S]#") $(basename "$0") '********' >>/var/log/go/$(basename "$0").log

Domain=$1
Password=$2

OS=$(</usr/local/goadmio/linux/os.db)

MySQLRootPassword=$(sudo grep -oP 'DBPass="\K[^"]+' /usr/local/goadmio/include/config/config.php)

if [ "$Domain" = "all" ]; then

    OUTPUT="Domain Password"

    cd /home
    for Domain in *; do

        Username=$(sudo sqlite3 -batch -init /root/.sqliterc /var/lib/sqlite/site.db "select Username from Site where Domain='$Domain'")

        if sudo test -d "/home/$Domain/www" ; then

            if [ "$Password" = "" ]; then

                Password=$(sudo openssl rand -base64 16 | tr -dc 'A-Za-z0-9' | head -c 16)

            fi

            sudo /go/password $Domain $Password

            OUTPUT="$OUTPUT\n$Username $Password"

        fi

    done

    echo -ne $OUTPUT | column -t

    exit 1

fi

Username=$(sudo sqlite3 -batch -init /root/.sqliterc /var/lib/sqlite/site.db "select Username from Site where Domain='$Domain'")

if sudo test ! -d "/home/$Domain" ; then
    echo "Directory /home/$Domain not exists."
    exit 1
fi

if [[ "$OS" = @(AlmaLinux|Rocky|CentOS|Red Hat|Fedora) ]]; then

    sudo groupdel $Username &>/dev/null
    sudo userdel -f $Username &>/dev/null

    sudo /go/adduser $Username $Password

    echo "Password has been modified successfully ($Domain)."

elif [[ "$OS" = @(Debian|Ubuntu) ]]; then

    sudo groupdel $Username &>/dev/null
    sudo userdel -f $Username &>/dev/null

    sudo /go/adduser $Username $Password

    echo "Password has been modified successfully ($Domain)."

fi

sudo screen -d -m bash -c "/go/fix $Domain"

# Change SSH Password
sudo /go/passwd $Domain

sudo systemctl restart vsftpd

Salt=$(openssl rand -hex 16 | md5sum | cut -d' ' -f1)
EncryptedPassword=$(echo "$Password|$Salt" | md5sum | cut -d' ' -f1)
sudo sqlite3 -batch -init /root/.sqliterc /var/lib/sqlite/site.db "update Site SET Password='$EncryptedPassword',Salt='$Salt' where Domain='$Domain'"

echo "Change default password for all MySQL Databases:"
sudo /go/phpmyadmin $Domain