<?php
session_start();
if (isset($_POST['password']) && $_POST['password'] === 'vandal123') {
    $_SESSION['logged_in'] = true;
    header('Location: panel.php');
    exit;
}
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login</title>
<style>*{margin:0;padding:0}body{background:#06060d;color:#fff;font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}
.card{background:#0d0d1a;border:1px solid #1a1a30;border-radius:16px;padding:30px;width:100%;max-width:360px;text-align:center}
h1{color:#ff3838;margin-bottom:20px}input{width:100%;padding:12px;background:#000;border:1px solid #1a1a30;border-radius:8px;color:#fff;margin-bottom:12px;text-align:center;font-size:16px}
button{width:100%;padding:14px;background:#ff3838;color:#fff;border:none;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer}
</style></head><body><div class="card"><h1>🔐 Panel Login</h1>
<form method="post"><input type="password" name="password" placeholder="Password" autofocus><button>Login</button></form></div></body></html>