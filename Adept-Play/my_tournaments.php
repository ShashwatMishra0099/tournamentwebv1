<?php
require_once __DIR__.'/common/config.php';
if(!$pdo) die('Run install.php first.');
if(!is_logged()) header('Location: login.php');
$uid = $_SESSION['user_id'];

// get tournaments the user joined
$stmt = $pdo->prepare('SELECT p.id as pid, t.* FROM participants p JOIN tournaments t ON p.tournament_id = t.id WHERE p.user_id = ? ORDER BY t.match_time DESC');
$stmt->execute([$uid]);
$joined = $stmt->fetchAll();

include __DIR__.'/common/header.php';
?>

<div class="flex gap-2 mb-4">
  <button id="tab-active" class="flex-1 py-2 rounded bg-indigo-600">Upcoming / Live</button>
  <button id="tab-completed" class="flex-1 py-2 rounded bg-gray-800">Completed</button>
</div>

<div id="panel-active">
  <?php foreach($joined as $j): if($j['status'] !== 'Completed'): ?>
    <div class="p-3 mb-3 rounded app-card border border-gray-800">
      <div class="flex justify-between">
        <div>
          <div class="text-sm text-gray-400"><?=htmlspecialchars($j['game_name'])?></div>
          <div class="font-medium"><?=htmlspecialchars($j['title'])?></div>
          <div class="text-xs text-gray-400"><?=htmlspecialchars(date('d M, Y H:i', strtotime($j['match_time'])))?></div>
        </div>
        <div class="text-right">
          <?php if($j['status']=='Live' || $j['status']=='Ongoing'): ?>
            <div class="text-xs text-gray-400">Room</div>
            <div class="font-semibold"><?=htmlspecialchars($j['room_id']?:'--')?></div>
            <div class="text-xs">PW: <?=htmlspecialchars($j['room_password']?:'--')?></div>
          <?php else: ?>
            <div class="text-sm text-gray-400">Status</div>
            <div class="font-semibold"><?=htmlspecialchars($j['status'])?></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; endforeach; ?>
</div>

<div id="panel-completed" class="hidden">
  <?php foreach($joined as $j): if($j['status'] === 'Completed'): ?>
    <div class="p-3 mb-3 rounded app-card border border-gray-800">
      <div class="flex justify-between">
        <div>
          <div class="text-sm text-gray-400"><?=htmlspecialchars($j['game_name'])?></div>
          <div class="font-medium"><?=htmlspecialchars($j['title'])?></div>
          <div class="text-xs text-gray-400">Played: <?=htmlspecialchars(date('d M, Y H:i', strtotime($j['match_time'])))?></div>
        </div>
        <div class="text-right">
          <div class="text-sm text-gray-400">Result</div>
          <?php
            // check if user was winner
            $stmt = $pdo->prepare('SELECT winner_user_id FROM tournaments WHERE id = ?');
            $stmt->execute([$j['id']]);
            $w = $stmt->fetchColumn();
            $result = ($w && intval($w) === intval($uid)) ? 'Winner' : 'Participated';
          ?>
          <div class="font-semibold"><?=$result?></div>
        </div>
      </div>
    </div>
  <?php endif; endforeach; ?>
</div>

<script>
const tabA = document.getElementById('tab-active');
const tabC = document.getElementById('tab-completed');
const panelA = document.getElementById('panel-active');
const panelC = document.getElementById('panel-completed');

tabA.addEventListener('click', ()=>{ tabA.classList.add('bg-indigo-600'); tabC.classList.remove('bg-indigo-600'); panelA.classList.remove('hidden'); panelC.classList.add('hidden'); });
tabC.addEventListener('click', ()=>{ tabC.classList.add('bg-indigo-600'); tabA.classList.remove('bg-indigo-600'); panelC.classList.remove('hidden'); panelA.classList.add('hidden'); });
</script>

<?php include __DIR__.'/common/bottom.php'; ?>
