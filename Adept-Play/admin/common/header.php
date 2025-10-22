<?php
require_once __DIR__.'/../../common/config.php';
$admin = null;
if($pdo && isset($_SESSION['admin_id'])){
    $stmt = $pdo->prepare('SELECT id, username FROM admin WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>Adept Play — Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style> body{ background:#0b0f12; color:#e6eef8; } </style>
<script>
document.addEventListener('contextmenu', e=>e.preventDefault());
</script>
</head>
<body class="min-h-screen antialiased select-none">
<header class="w-full p-4 border-b border-gray-800">
  <div class="max-w-5xl mx-auto flex justify-between items-center">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-gray-800 flex items-center justify-center text-indigo-400"><i class="fa-solid fa-shield-halved"></i></div>
      <div>
        <div class="font-semibold text-lg">Adept Play — Admin</div>
        <div class="text-xs text-gray-400">Manage site and tournaments</div>
      </div>
    </div>
    <div>
      <?php if($admin): ?>
        <span class="text-sm text-gray-400 mr-3">Hi, <?=htmlspecialchars($admin['username'])?></span>
        <a href="login.php?logout=1" class="px-3 py-2 rounded bg-red-700 text-sm">Logout</a>
      <?php endif; ?>
    </div>
  </div>
</header>
<main class="max-w-5xl mx-auto p-4">
