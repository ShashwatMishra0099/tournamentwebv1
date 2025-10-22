<?php
require_once __DIR__.'/common/header.php';
if(!isset($_SESSION['admin_id'])){ header('Location: login.php'); exit; }

if(isset($_GET['block'])){
    $id = intval($_GET['block']);
    $stmt = $pdo->prepare('UPDATE users SET is_blocked = 1 WHERE id = ?'); $stmt->execute([$id]);
    header('Location: user.php'); exit;
}
if(isset($_GET['unblock'])){
    $id = intval($_GET['unblock']);
    $stmt = $pdo->prepare('UPDATE users SET is_blocked = 0 WHERE id = ?'); $stmt->execute([$id]);
    header('Location: user.php'); exit;
}

$stmt = $pdo->query('SELECT id, username, email, wallet_balance, is_blocked FROM users ORDER BY id DESC'); $users = $stmt->fetchAll();
?>

<div class="p-3 rounded app-card border border-gray-800">
  <h3 class="font-semibold mb-2">Users</h3>
  <?php foreach($users as $u): ?>
    <div class="p-2 mb-2 rounded border border-gray-800 flex justify-between items-center">
      <div>
        <div class="font-medium"><?=htmlspecialchars($u['username'])?></div>
        <div class="text-xs text-gray-400"><?=htmlspecialchars($u['email'])?></div>
      </div>
      <div class="text-right">
        <div class="text-sm font-semibold"><?=rupee($u['wallet_balance'])?></div>
        <?php if($u['is_blocked']): ?>
          <a href="?unblock=<?=$u['id']?>" class="text-xs text-green-400">Unblock</a>
        <?php else: ?>
          <a href="?block=<?=$u['id']?>" class="text-xs text-red-400">Block</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__.'/common/bottom.php'; ?>
