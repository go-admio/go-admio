#!/bin/bash

echo "$(date +"[%b %d %H:%M:%S]#") $(basename "$0") $@" | sudo tee -a "/var/log/go/$(basename "$0").log" >/dev/null

OS=$(</usr/local/goadmio/linux/os.db)
PORT=$1

netstat -tulpn >/usr/local/goadmio/tmp/ports.db
if grep -q ":$PORT " /usr/local/goadmio/tmp/ports.db; then
    if [ "$PORT" != "22" ]; then

        echo "Port $PORT in use."
        exit 1

    fi
fi

# Remove all lines starts with "Port"
sudo sed -i '/^Port/ d' /etc/ssh/sshd_config

# Insert a text at the beginning of a file
sudo sed -i "1s/^/Port $PORT\n/" /etc/ssh/sshd_config

sudo screen -d -m bash -c "/go/allow $PORT"

sudo sqlite3 -batch -init /root/.sqliterc /var/lib/sqlite/config.db "UPDATE Config SET SSHPort='$PORT' WHERE ConfigID=1"

sudo systemctl restart sshd

echo "OpenSSH Port has been changed successfully."
