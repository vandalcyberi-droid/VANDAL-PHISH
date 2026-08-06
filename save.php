<?php
header('Content-Type: application/json');
$type=$_GET['type']??'unknown';$raw=file_get_contents('php://input');$data=json_decode($raw,true);$ts=date('Ymd_His');$r=bin2hex(random_bytes(2));
if($type==='webcam'&&isset($data['image'])){$img=substr($data['image'],strpos($data['image'],',')+1);file_put_contents("webcam_{$ts}_{$r}.jpg",base64_decode($img));}
else{file_put_contents("{$type}_{$ts}_{$r}.json",json_encode($data,JSON_UNESCAPED_UNICODE));}
echo json_encode(['status'=>'ok']);