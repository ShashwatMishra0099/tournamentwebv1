<?php
require_once __DIR__.'/common/header.php';
if(!isset($_SESSION['admin_id'])){ header('Location: login.php'); exit; }

$id = intval($_GET['id'] ?? 0);
if(!$id) { echo '<div class="text-red-400">Invalid tournament ID.</div>'; include __DIR__.'/common/bottom.php'; exit; }

// fetch tournament
$stmt = $pdo->prepare('SELECT * FROM tournaments WHERE id = ?'); $stmt->execute([$id]); $t = $stmt->fetch();
if(!$t){ echo '<div class="text-red-400">Tournament not found.</div>'; include __DIR__.'/common/bottom.php'; exit; }
$messages=[];

// update room
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_room'])){
    $room = trim($_POST['room_id'] ?? '');
    $pw = trim($_POST['room_password'] ?? '');
    $stmt = $pdo->prepare('UPDATE tournaments SET room_id=?, room_password=?, status = ? WHERE id = ?');
    $stmt->execute([$room,$pw, 'Live', $id]);
    $messages[] = ['type'=>'success','text'=>'Room updated and tournament set to Live.'];
}

// declare winner
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['declare_winner'])){
    $winner_id = intval($_POST['winner_user'] ?? 0);
    if(!$winner_id) $messages[] = ['type'=>'error','text'=>'Select a winner.'];
    else {
        // compute commission and winner amount
        $commission = ($t['commission_percentage'] / 100) * $t['prize_pool'];
        $winner_amount = $t['prize_pool'] - $commission;
        $pdo->beginTransaction();
        try{
            // credit winner
            $stmt = $pdo->prepare('UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?');
            $stmt->execute([$winner_amount, $winner_id]);
            // transaction record
            $stmt = $pdo->prepare('INSERT INTO transactions (user_id, amount, type, description, created_at) VALUES (?,?,?,?,NOW())');
            $stmt->execute([$winner_id, $winner_amount, 'credit', 'Prize for tournament #'.$id]);
            // mark tournament completed and set winner
            $stmt = $pdo->prepare('UPDATE tournaments SET status = ?, winner_user_id = ? WHERE id = ?');
            $stmt->execute(['Completed', $winner_id, $id]);
            $pdo->commit();
            $messages[] = ['type'=>'success','text'=>'Winner declared and prize distributed.'];
        } catch(Exception $e){ $pdo->rollBack(); $messages[] = ['type'=>'error','text'=>'Failed to distribute prize.']; }
    }
}

// fetch participants
$stmt = $pdo->prepare('SELECT u.id, u.username, u.email FROM participants p JOIN users u ON p.user_id = u.id WHERE p.tournament_id = ?');
$stmt->execute([$id]); $participants = $stmt->fetchAll();

?>

<div class="p-3 rounded app-card border border-gray-800 mb-3">
  <div class="flex justify-between items-center">
    <div>
      <div class="text-xs text-gray-400"><?=htmlspecialchars($t['game_name'])?></div>
      <div class="font-semibold text-lg"><?=htmlspecialchars($t['title'])?></div>
      <div class="text-xs text-gray-400">Match: <?=htmlspecialchars($t['match_time'])?></div>
    </div>
    <div class="text-right">
      <div class="text-xs text-gray-400">Status</div>
      <div class="font-semibold"><?=htmlspecialchars($t['status'])?></div>
    </div>
  </div>
</div>

<?php foreach($messages as $m): ?>
  <div class="mb-2 p-2 rounded <?php echo $m['type']=='success' ? 'bg-green-900/30 border border-green-700' : 'bg-red-900/30 border border-red-700'; ?>"><?=htmlspecialchars($m['text'])?></div>
<?php endforeach; ?>

<div class="grid grid-cols-1 gap-3">
  <div class="p-3 rounded app-card border border-gray-800">
    <h4 class="font-semibold mb-2">Update Room</h4>
    <form method="POST">
      <input type="hidden" name="update_room" value="1">
      <label class="text-xs">Room ID</label>
      <input name="room_id" value="<?=htmlspecialchars($t['room_id'])?>" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <label class="text-xs mt-2">Room Password</label>
      <input name="room_password" value="<?=htmlspecialchars($t['room_password'])?>" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800" />
      <button class="mt-3 w-full py-2 rounded bg-indigo-600">Update Room</button>
    </form>
  </div>

  <div class="p-3 rounded app-card border border-gray-800">
    <h4 class="font-semibold mb-2">Participants (<?=count($participants)?>)</h4>
    <?php if(empty($participants)): ?>
      <div class="p-3">No participants yet.</div>
    <?php else: ?>
      <?php foreach($participants as $p): ?>
        <div class="p-2 border-b border-gray-800 flex justify-between">
          <div>
            <div class="font-medium"><?=htmlspecialchars($p['username'])?></div>
            <div class="text-xs text-gray-400"><?=htmlspecialchars($p['email'])?></div>
          </div>
          <div class="text-sm text-gray-400">ID <?=$p['id']?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="p-3 rounded app-card border border-gray-800">
    <h4 class="font-semibold mb-2">Declare Winner</h4>
    <form method="POST">
      <input type="hidden" name="declare_winner" value="1">
      <label class="text-xs">Winner</label>
      <select name="winner_user" class="w-full rounded p-2 mt-1 bg-gray-900 border border-gray-800">
        <option value="">-- Select --</option>
        <?php foreach($participants as $p): ?>
          <option value="<?=$p['id']?>"><?=htmlspecialchars($p['username'])?> (ID <?=$p['id']?>)</option>
        <?php endforeach; ?>
      </select>
      <button class="mt-3 w-full py-2 rounded bg-indigo-600">Declare Winner & Distribute Prize</button>
    </form>
  </div>
</div>

<?php include __DIR__.'/common/bottom.php'; ?>
