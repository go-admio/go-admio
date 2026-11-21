#!/bin/bash

echo "$(date +"[%b %d %H:%M:%S]#") $(basename "$0") $@" | sudo tee -a "/var/log/go/$(basename "$0").log" >/dev/null


DELAY=15
BAR_WIDTH=50

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# --- Require root ---
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Error: This script must be run as root (use sudo).${NC}"
    exit 1
fi

clear
echo -e "${RED}==================================================${NC}"
echo -e "${RED}             SYSTEM REBOOT SCHEDULED              ${NC}"
echo -e "${RED}==================================================${NC}"
echo
echo -e "${YELLOW}The server will reboot in ${DELAY} seconds.${NC}"
echo -e "${YELLOW}Press Ctrl+C to cancel.${NC}"
echo

for ((i = 0; i <= DELAY; i++)); do
    # Calculate percentage and bar length
    percent=$(( i * 100 / DELAY ))
    filled=$(( i * BAR_WIDTH / DELAY ))
    empty=$(( BAR_WIDTH - filled ))

    bar_fill=$(printf "%${filled}s" | tr ' ' '#')
    bar_empty=$(printf "%${empty}s" | tr ' ' '-')

    # Print on same line (\r = carriage return)
    printf "\r${CYAN}[${GREEN}%s${NC}${CYAN}%s${NC}${CYAN}]${NC} %3d%%  ${YELLOW}Rebooting in %2d s${NC}  " \
        "$bar_fill" "$bar_empty" "$percent" $(( DELAY - i ))

    # Wait 1 second, but skip wait on the last iteration
    if [ "$i" -lt "$DELAY" ]; then
        sleep 1
    fi
done

echo
echo
echo -e "${RED}Rebooting now...${NC}"
sleep 1

if command -v systemctl >/dev/null 2>&1; then
    systemctl reboot
else
    shutdown -r now
fi
