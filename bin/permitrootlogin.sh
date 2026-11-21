#!/bin/bash

echo "$(date +"[%b %d %H:%M:%S]#") $(basename "$0") $@" | sudo tee -a "/var/log/go/$(basename "$0").log" >/dev/null
IPv4=$(curl -s -4 checkip.amazonaws.com)
IPv6=$(curl -s -6 https://icanhazip.com)

WS=$(</usr/local/goadmio/linux/ws.db)
OS=$(</usr/local/goadmio/linux/os.db)

TimeStamp=$(date +'%s')

IP=$1

if [ "$IP" = "$IPv4" ] || [ "$IP" = "$IPv6" ]; then
    echo "Cannot block server IP address."
    exit 1
fi

if [[ ! $IP =~ ^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$ ]] && [[ ! $IP =~ ^[0-9]+\.[0-9]+\.[0-9]+\.0/24$ ]] && [[ ! $IP =~ ^([0-9a-fA-F]{0,4}:){1,7}[0-9a-fA-F]{0,4}$ ]]; then
    echo "Invalid IP \"$IP\""
    exit 1
fi

if [[ "$@" == *"--delete"* ]]; then

    sudo sqlite3 -batch -init /root/.sqliterc /var/lib/sqlite/ssh.db "DELETE FROM SSH WHERE IP='$IP'"
    echo "IP $IP deleted successfully."

else

    sudo sqlite3 -batch -init /root/.sqliterc /var/lib/sqlite/ssh.db "INSERT OR IGNORE INTO SSH (IP,Directive,Value,TimeStamp) VALUES ('$IP','PermitRootLogin','yes','$TimeStamp')"
    echo "IP $IP added successfully. This IP is now allowed to access the server."

fi

sudo screen -d -m bash -c "/go/ssh"