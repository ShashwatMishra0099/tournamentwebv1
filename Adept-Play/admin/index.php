<?php
require_once __DIR__.'/common/header.php';
if(!isset($_SESSION['admin_id'])){ header('Location: login.php'); exit; }

// stats
$stmt = $pdo->query('SELECT COUNT(*) FROM users'); $totalUsers = $stmt->fetchColumn();
$stmt = $pdo->query('SELECT COUNT(*) FROM tournaments'); $totalTournaments = $stmt->fetchColumn();
$stmt = $pdo->query("SELECT IFNULL(SUM(prize_pool),0) FROM tournaments WHERE status='Completed'"); $totalPrize = $stmt->fetchColumn();
// revenue = sum of commission applied on completed tournaments
$stmt = $pdo->query("SELECT IFNULL(SUM((commission_percentage/100) * prize_pool),0) FROM tournaments WHERE status='Completed'"); $totalRevenue = $stmt->fetchColumn();

?>
<div class="grid grid-cols-2 gap-3 mb-4">
  <div class="p-3 rounded app-card border border-gray-800">
    <div class="text-xs text-gray-400">Total Users</div>
    <div class="text-2xl font-semibold"><?=intval($totalUsers)?></div>
  </div>
  <div class="p-3 rounded app-card border border-gray-800">
    <div class="text-xs text-gray-400">Total Tournaments</div>
    <div class="text-2xl font-semibold"><?=intval($totalTournaments)?></div>
  </div>
  <div class="p-3 rounded app-card border border-gray-800">
    <div class="text-xs text-gray-400">Prize Distributed</div>
    <div class="text-2xl font-semibold"><?=rupee($totalPrize)?></div>
  </div>
  <div class="p-3 rounded app-card border border-gray-800">
    <div class="text-xs text-gray-400">Total Revenue</div>
    <div class="text-2xl font-semibold"><?=rupee($totalRevenue)?></div>
  </div>
</div>

<div class="mb-4">
  <a href="tournament.php" class="py-2 px-3 rounded bg-indigo-600">Create New Tournament</a>
  <a href="user.php" class="ml-2 py-2 px-3 rounded bg-gray-800">Manage Users</a>
</div>

<?php include __DIR__.'/common/bottom.php'; ?>
