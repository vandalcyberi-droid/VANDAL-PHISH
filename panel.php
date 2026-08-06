<?php
$cams = glob('webcam_*.jpg');
rsort($cams);
if (isset($_GET['del'])) {
    $n = basename($_GET['del']);
    if (is_file($n)) { unlink($n); }
    header('Location: panel.php'); exit;
}
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta http-equiv="refresh" content="6"><title>VANDAL Panel</title>
<style>*{margin:0;padding:0}body{background:#06060d;color:#c8c8d8;font-family:system-ui,sans-serif;padding:20px}
h1{color:#ff3838;font-size:20px}.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-top:16px}
.card{background:#0d0d1a;border:1px solid #1a1a30;border-radius:10px;padding:6px;text-align:center}
.card img{width:100%;border-radius:6px;cursor:pointer}.card div{font-size:10px;color:#888;margin-top:5px}.card a{color:#ff3838;text-decoration:none}
</style></head><body><h1>📸 VANDAL Panel — <?=count($cams)?> photos</h1>
<div class="grid"><?php foreach($cams as $c): ?><div class="card"><img src="<?=$c?>" onclick="window.open(this.src)"><div><?=$c?> <a href="?del=<?=$c?>">×</a></div></div><?php endforeach; ?></div>
</body></html>