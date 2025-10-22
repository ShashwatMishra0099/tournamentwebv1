<?php
require_once __DIR__.'/common/config.php';
if(!$pdo) die('Run install.php first.');
if(!is_logged()) header('Location: login.php');
$user = current_user($pdo);

// fetch transactions
$stmt = $pdo->prepare('SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$tx = $stmt->fetchAll();

include __DIR__.'/common/header.php';
?>

<div class="p-4 rounded app-card border border-gray-800 mb-4">
  <div class="text-xs text-gray-400">Current Balance</div>
  <div class="text-2xl font-semibold"><?=rupee($user['wallet_balance'])?></div>
  <div class="mt-3 flex gap-2">
    <button class="flex-1 py-2 rounded bg-indigo-600">Add Money</button>
    <button class="flex-1 py-2 rounded bg-gray-800">Withdraw</button>
  </div>
</div>

<div>
  <div class="text-sm text-gray-400 mb-2">Transaction History</div>
  <?php if(empty($tx)): ?>
    <div class="p-3 rounded app-card">No transactions yet.</div>
  <?php endif; ?>
  <?php foreach($tx as $t): ?>
    <div class="p-3 mb-2 rounded app-card border border-gray-800 flex justify-between">
      <div>
        <div class="text-sm"><?=htmlspecialchars($t['description'])?></div>
        <div class="text-xs text-gray-400"><?=htmlspecialchars($t['created_at'])?></div>
      </div>
      <div class="text-right">
        <div class="font-semibold"><?= $t['type'] === 'credit' ? '+' . rupee($t['amount']) : '-' . rupee($t['amount']) ?></div>
        <div class="text-xs text-gray-400"><?=$t['type']?></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include __DIR__.'/common/bottom.php'; ?>
