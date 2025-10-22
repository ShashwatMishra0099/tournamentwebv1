<?php
require_once __DIR__.'/config.php';
$user = null;
$balance_display = '';
if($pdo && is_logged()){
    $user = current_user($pdo);
    $balance_display = isset($user['wallet_balance']) ? rupee($user['wallet_balance']) : rupee(0);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<title>Adept Play</title>
<!-- Tailwind Play CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
  /* extra app-like tweaks */
  body{ background:#0b0f12; color:#e6eef8; -webkit-font-smoothing:antialiased; }
  .app-card{ background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)); }
</style>
<script>
// Disable text selection, right click, zoom
document.addEventListener('DOMContentLoaded', function(){
  document.documentElement.style.webkitUserSelect = 'none';
  document.documentElement.style.userSelect = 'none';
  document.documentElement.style.msUserSelect = 'none';

  // disable right click
  document.addEventListener('contextmenu', function(e){ e.preventDefault(); });

  // disable pinch zoom / ctrl+ / ctrl- / meta+ / meta-
  window.addEventListener('keydown', function(e){
    if((e.ctrlKey || e.metaKey) && (e.key === '+' || e.key === '-' || e.key === '=')){
      e.preventDefault();
    }
    if(e.key === 'F12') e.preventDefault();
  });
  window.addEventListener('wheel', function(e){
    if(e.ctrlKey) e.preventDefault();
  }, {passive:false});
});
</script>
</head>
<body class="min-h-screen antialiased select-none overflow-hidden">

<header class="w-full sticky top-0 z-50 bg-gradient-to-b from-gray-900 to-transparent border-b border-gray-800 p-4">
  <div class="max-w-md mx-auto flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-gray-800 flex items-center justify-center text-indigo-400">
        <i class="fa-solid fa-gamepad"></i>
      </div>
      <div>
        <div class="font-semibold text-lg">Adept Play</div>
        <div class="text-xs text-gray-400">Tournaments & Play</div>
      </div>
    </div>

    <div class="flex items-center gap-3">
      <?php if($user): ?>
        <div class="text-right">
          <div class="text-xs text-gray-400">Wallet</div>
          <div class="text-sm font-medium"><?=htmlspecialchars($balance_display)?></div>
        </div>
        <a href="profile.php" class="p-2 rounded-lg bg-gray-800">
          <i class="fa-solid fa-user text-gray-300"></i>
        </a>
      <?php else: ?>
        <a href="login.php" class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm">Login</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="max-w-md mx-auto p-4 pb-28">

<!-- page content starts -->
